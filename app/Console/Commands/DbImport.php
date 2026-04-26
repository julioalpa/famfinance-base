<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbImport extends Command
{
    protected $signature   = 'db:import {path : Ruta del archivo JSON exportado}';
    protected $description = 'Importa datos desde un archivo JSON generado por db:export';

    // Orden de truncado: hijos antes que padres (inverso al de inserción)
    private const TRUNCATE_ORDER = [
        'monthly_payments',
        'payment_items',
        'recurring_expense_logs',
        'recurring_expenses',
        'installments',
        'transactions',
        'exchange_rates',
        'categories',
        'accounts',
        'invitations',
        'family_group_user',
        'family_groups',
        'users',
    ];

    private const INSERT_ORDER = [
        'users',
        'family_groups',
        'family_group_user',
        'invitations',
        'accounts',
        'categories',
        'exchange_rates',
        'recurring_expenses',
        'transactions',
        'installments',
        'recurring_expense_logs',
        'payment_items',
        'monthly_payments',
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

        if (! $this->confirm('Esto BORRARÁ todos los datos actuales de la DB destino. ¿Continuar?')) {
            return self::SUCCESS;
        }

        // Truncar en orden inverso
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
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

        // Deshabilitar FK checks durante la inserción
        if ($driver === 'pgsql') {
            DB::statement("SET session_replication_role = 'replica'");
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
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

        // Re-habilitar FK checks
        if ($driver === 'pgsql') {
            DB::statement("SET session_replication_role = 'origin'");
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->newLine();
        $this->info('¡Importación completa!');

        return self::SUCCESS;
    }
}
