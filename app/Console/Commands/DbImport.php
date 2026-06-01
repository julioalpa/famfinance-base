<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbImport extends Command
{
    protected $signature   = 'db:import {path : Ruta del archivo JSON exportado} {--force : No pedir confirmación}';
    protected $description = 'Importa datos desde un archivo JSON generado por db:export';

    private const INSERT_ORDER = [
        'users',
        'family_groups',
        'family_group_user',
        'invitations',
        'accounts',
        'categories',
        'exchange_rates',
        'promotions',
        'tags',
        'tag_groups',
        'tag_group_tag',
        'recurring_expenses',
        'payment_items',
        'transactions',
        'installments',
        'loan_installments',
        'monthly_payments',
        'recurring_expense_logs',
        'taggables',
    ];

    // Orden de truncado: hijos antes que padres (inverso al de inserción)
    private const TRUNCATE_ORDER = [
        'taggables',
        'recurring_expense_logs',
        'monthly_payments',
        'loan_installments',
        'installments',
        'transactions',
        'payment_items',
        'recurring_expenses',
        'tag_group_tag',
        'tag_groups',
        'tags',
        'promotions',
        'exchange_rates',
        'categories',
        'accounts',
        'invitations',
        'family_group_user',
        'family_groups',
        'users',
    ];

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! file_exists($path)) {
            $this->error("Archivo no encontrado: {$path}");
            return self::FAILURE;
        }

        $export = json_decode(file_get_contents($path), true);

        if (! isset($export['tables'])) {
            $this->error('Formato de archivo inválido.');
            return self::FAILURE;
        }

        $driver = DB::getDriverName();

        $this->info("Importando en {$driver} (exportado desde {$export['driver']} el {$export['exported_at']})...");
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Esto BORRARÁ todos los datos actuales de la DB destino. ¿Continuar?')) {
            return self::SUCCESS;
        }

        // Truncar en orden inverso
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        foreach (self::TRUNCATE_ORDER as $table) {
            if (isset($export['tables'][$table])) {
                if ($driver === 'pgsql') {
                    DB::statement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE");
                } else {
                    DB::table($table)->truncate();
                }
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Insertar en orden de dependencias
        foreach (self::INSERT_ORDER as $table) {
            $rows = $export['tables'][$table] ?? [];

            if (empty($rows)) {
                $this->line("  <fg=yellow>–</> {$table}: vacío, se omite");
                continue;
            }

            // Insertar en chunks para no superar límites de parámetros
            $chunks = array_chunk($rows, 100);
            foreach ($chunks as $chunk) {
                DB::table($table)->insert($chunk);
            }

            $this->line("  <fg=green>✓</> {$table}: " . count($rows) . ' registros insertados');
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->newLine();
        $this->info('¡Importación completa!');

        return self::SUCCESS;
    }
}
