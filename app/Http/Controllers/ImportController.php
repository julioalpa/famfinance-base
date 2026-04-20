<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
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

        // Detectar y convertir encoding
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }
        // Quitar BOM si existe
        $content = ltrim($content, "\xEF\xBB\xBF");

        $lines = array_values(array_filter(
            explode("\n", str_replace("\r\n", "\n", $content)),
            fn($l) => trim($l) !== ''
        ));

        if (count($lines) < 2) {
            return back()->withErrors(['file' => 'El archivo está vacío o solo tiene encabezado.']);
        }

        // Saltar fila de encabezado
        array_shift($lines);

        $imported          = 0;
        $skipped           = 0;
        $createdCategories = [];
        $createdAccounts   = [];
        $categoryCache     = [];
        $accountCache      = [];

        foreach ($lines as $line) {
            $row = str_getcsv(trim($line));

            if (count($row) < 7) {
                $skipped++;
                continue;
            }

            [$catName, $nota, $importe, $moneda, $tipo, $cuenta, $fecha] = $row;

            $catName = trim($catName);
            $nota    = trim($nota);
            $importe = trim($importe);
            $moneda  = trim($moneda);
            $tipo    = trim($tipo);
            $cuenta  = trim($cuenta);
            $fecha   = trim($fecha);

            // Saltar transferencias y filas sin cuenta
            if (mb_strtolower($tipo) === 'transferencia' || $cuenta === '') {
                $skipped++;
                continue;
            }

            $type = match (mb_strtolower($tipo)) {
                'gastos'   => 'expense',
                'ingresos' => 'income',
                default    => null,
            };

            if (!$type) {
                $skipped++;
                continue;
            }

            // Parsear moneda y monto
            $currency = str_starts_with($moneda, 'USD') ? 'USD' : 'ARS';

            if ($currency === 'USD' && preg_match('/\(([0-9.\s]+)\)/', $moneda, $m)) {
                // Usar el monto en USD del campo Moneda
                $amount = (float) str_replace(' ', '', $m[1]);
            } else {
                // Monto en ARS del campo Importe (espacios como separador de miles)
                $amount = (float) str_replace([' ', ','], ['', '.'], $importe);
            }

            if ($amount <= 0) {
                $skipped++;
                continue;
            }

            // Fecha: YYYY.MM.DD → YYYY-MM-DD
            $date = str_replace('.', '-', $fecha);

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

            // Cuenta: buscar o crear
            $acctKey = mb_strtolower($cuenta);
            if (!isset($accountCache[$acctKey])) {
                $account = Account::where('family_group_id', $groupId)
                    ->whereRaw('LOWER(name) = ?', [$acctKey])
                    ->first();

                if (!$account) {
                    $account = Account::create([
                        'family_group_id' => $groupId,
                        'user_id'         => $userId,
                        'name'            => $cuenta,
                        'type'            => 'digital',
                        'currency'        => $currency,
                        'is_active'       => true,
                    ]);
                    $createdAccounts[] = $cuenta;
                }

                $accountCache[$acctKey] = $account->id;
            }

            Transaction::create([
                'family_group_id' => $groupId,
                'user_id'         => $userId,
                'account_id'      => $accountCache[$acctKey],
                'category_id'     => $categoryCache[$catKey],
                'type'            => $type,
                'amount'          => $amount,
                'currency'        => $currency,
                'date'            => $date,
                'description'     => $nota ?: null,
            ]);

            $imported++;
        }

        return back()->with('results', [
            'imported'          => $imported,
            'skipped'           => $skipped,
            'createdCategories' => array_unique($createdCategories),
            'createdAccounts'   => array_unique($createdAccounts),
        ]);
    }
}
