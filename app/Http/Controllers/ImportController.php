<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    private static function parseDate(string $fecha): ?string
    {
        $fecha = trim($fecha);

        // Formato YYYY.MM.DD
        if (preg_match('/^\d{4}\.\d{2}\.\d{2}$/', $fecha)) {
            return str_replace('.', '-', $fecha);
        }

        // Formato "16 abr 2026"
        $months = ['ene'=>'01','feb'=>'02','mar'=>'03','abr'=>'04','may'=>'05','jun'=>'06',
                   'jul'=>'07','ago'=>'08','sep'=>'09','oct'=>'10','nov'=>'11','dic'=>'12'];

        if (preg_match('/^(\d{1,2})\s+([a-záéíóú]{3})\s+(\d{4})$/i', $fecha, $m)) {
            $mon = $months[mb_strtolower($m[2])] ?? null;
            if ($mon) {
                return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$mon, (int)$m[1]);
            }
        }

        return null;
    }

    public function index()
    {
        return view('import.index');
    }

    private static function parseAmount(string $importe, string $moneda): float
    {
        $currency = str_starts_with($moneda, 'USD') ? 'USD' : 'ARS';

        if ($currency === 'USD' && preg_match('/\(([0-9.\s]+)\)/', $moneda, $m)) {
            return (float) str_replace(' ', '', $m[1]);
        }

        // Preservar signo (para detectar dirección en transferencias)
        return (float) str_replace([' ', ','], ['', '.'], $importe);
    }

    private function resolveAccount(string $name, string $currency, int $groupId, int $userId, array &$cache, array &$created): int
    {
        $key = mb_strtolower($name);

        if (!isset($cache[$key])) {
            $account = Account::where('family_group_id', $groupId)
                ->whereRaw('LOWER(name) = ?', [$key])
                ->first();

            if (!$account) {
                $account = Account::create([
                    'family_group_id' => $groupId,
                    'user_id'         => $userId,
                    'name'            => $name,
                    'type'            => 'digital',
                    'currency'        => $currency,
                    'is_active'       => true,
                ]);
                $created[] = $name;
            }

            $cache[$key] = $account->id;
        }

        return $cache[$key];
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'file.required' => 'Seleccioná un archivo CSV.',
            'file.mimes'    => 'El archivo debe ser CSV.',
            'file.max'      => 'El archivo no puede superar los 5MB.',
        ]);

        $groupId = session('active_family_group_id');
        $userId  = auth()->id();

        $content = file_get_contents($request->file('file')->getRealPath());

        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }
        $content = ltrim($content, "\xEF\xBB\xBF");

        $lines = array_values(array_filter(
            explode("\n", str_replace("\r\n", "\n", $content)),
            fn($l) => trim($l) !== ''
        ));

        if (count($lines) < 2) {
            return back()->withErrors(['file' => 'El archivo está vacío o solo tiene encabezado.']);
        }

        array_shift($lines);

        $imported          = 0;
        $skipped           = 0;
        $createdCategories = [];
        $createdAccounts   = [];
        $categoryCache     = [];
        $accountCache      = [];
        $transferBuffer    = [];
        $unpairedTransfers = 0;

        foreach ($lines as $line) {
            $row = str_getcsv(trim($line));

            if (count($row) < 7) {
                $skipped++;
                continue;
            }

            [$catName, $nota, $importe, $moneda, $tipo, $cuenta, $fecha] = array_map('trim', $row);

            if ($cuenta === '') {
                $skipped++;
                continue;
            }

            $currency = str_starts_with($moneda, 'USD') ? 'USD' : 'ARS';
            $date     = self::parseDate($fecha);

            if (!$date) {
                $skipped++;
                continue;
            }

            // ── Transferencias: acumular en buffer para emparejar ─────────────
            if (mb_strtolower($tipo) === 'transferencia') {
                $signedAmount = self::parseAmount($importe, $moneda);
                if ($signedAmount == 0) { $skipped++; continue; }

                $accountId = $this->resolveAccount($cuenta, $currency, $groupId, $userId, $accountCache, $createdAccounts);

                $transferBuffer[] = [
                    'account_id'  => $accountId,
                    'amount'      => $signedAmount,         // con signo para detectar dirección
                    'abs_amount'  => abs($signedAmount),
                    'date'        => $date,
                    'currency'    => $currency,
                    'description' => $nota ?: null,
                ];
                continue;
            }

            // ── Gastos e ingresos ─────────────────────────────────────────────
            $type = match (mb_strtolower($tipo)) {
                'gastos'   => 'expense',
                'ingresos' => 'income',
                default    => null,
            };

            if (!$type) {
                $skipped++;
                continue;
            }

            $amount = abs(self::parseAmount($importe, $moneda));
            if ($amount <= 0) { $skipped++; continue; }

            // Categoría: buscar o crear
            $catKey = mb_strtolower($catName);
            if (!isset($categoryCache[$catKey])) {
                $category = Category::where('family_group_id', $groupId)
                    ->whereRaw('LOWER(name) = ?', [$catKey])
                    ->first()
                    ?? Category::where('is_system', true)
                        ->whereRaw('LOWER(name) = ?', [$catKey])
                        ->first();

                if (!$category) {
                    $category = Category::create([
                        'family_group_id' => $groupId,
                        'name'            => $catName,
                        'type'            => $type === 'expense' ? 'expense' : 'income',
                        'is_system'       => false,
                    ]);
                    $createdCategories[] = $catName;
                }

                $categoryCache[$catKey] = $category->id;
            }

            $accountId = $this->resolveAccount($cuenta, $currency, $groupId, $userId, $accountCache, $createdAccounts);

            Transaction::create([
                'family_group_id' => $groupId,
                'user_id'         => $userId,
                'account_id'      => $accountId,
                'category_id'     => $categoryCache[$catKey],
                'type'            => $type,
                'amount'          => $amount,
                'currency'        => $currency,
                'date'            => $date,
                'description'     => $nota ?: null,
            ]);

            $imported++;
        }

        // ── Emparejar transferencias ──────────────────────────────────────────
        // Agrupar por fecha + |monto|. Cada par de filas con mismo día y monto
        // es una única transferencia: monto negativo = origen, positivo = destino.
        $groups = [];
        foreach ($transferBuffer as $t) {
            $key = $t['date'] . '_' . number_format($t['abs_amount'], 2, '.', '');
            $groups[$key][] = $t;
        }

        foreach ($groups as $group) {
            if (count($group) !== 2) {
                // No emparejables (fila suelta o más de 2 con mismo día+monto)
                $skipped           += count($group);
                $unpairedTransfers += count($group);
                continue;
            }

            [$t1, $t2] = $group;

            // Determinar origen y destino por signo; si ambos positivos, t1 = origen
            if ($t1['amount'] < 0) {
                [$from, $to] = [$t1, $t2];
            } elseif ($t2['amount'] < 0) {
                [$from, $to] = [$t2, $t1];
            } else {
                [$from, $to] = [$t1, $t2];
            }

            if ($from['account_id'] === $to['account_id']) {
                $skipped += 2;
                $unpairedTransfers += 2;
                continue;
            }

            Transaction::create([
                'family_group_id'   => $groupId,
                'user_id'           => $userId,
                'account_id'        => $from['account_id'],
                'target_account_id' => $to['account_id'],
                'type'              => 'transfer',
                'amount'            => $from['abs_amount'],
                'currency'          => $from['currency'],
                'date'              => $from['date'],
                'description'       => $from['description'] ?: 'Transferencia',
            ]);

            $imported++;
        }

        return back()->with('results', [
            'imported'          => $imported,
            'skipped'           => $skipped,
            'unpairedTransfers' => $unpairedTransfers,
            'createdCategories' => array_unique($createdCategories),
            'createdAccounts'   => array_unique($createdAccounts),
        ]);
    }
}
