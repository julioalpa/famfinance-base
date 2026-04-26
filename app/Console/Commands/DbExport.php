<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbExport extends Command
{
    protected $signature   = 'db:export {--path= : Ruta del archivo de salida}';
    protected $description = 'Exporta todas las tablas de la aplicación a un archivo JSON';

    // Tablas a exportar, en orden de dependencias (padres antes que hijos)
    private const TABLES = [
        'users',
        'family_groups',
        'family_group_user',
        'invitations',
        'accounts',
        'categories',
        'exchange_rates',
        'transactions',
        'installments',
        'recurring_expenses',
        'recurring_expense_logs',
        'payment_items',
        'monthly_payments',
    ];

    public function handle(): int
    {
        $path = $this->option('path') ?? storage_path('app/export_' . now()->format('Ymd_His') . '.json');

        $this->info('Exportando base de datos...');

        $export = [
            'exported_at' => now()->toISOString(),
            'driver'      => DB::getDriverName(),
            'tables'      => [],
        ];

        foreach (self::TABLES as $table) {
            $rows = DB::table($table)->get()->map(fn($r) => (array) $r)->toArray();
            $export['tables'][$table] = $rows;
            $this->line("  <fg=green>✓</> {$table}: " . count($rows) . ' registros');
        }

        file_put_contents($path, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info("Exportación completa → {$path}");

        return self::SUCCESS;
    }
}
