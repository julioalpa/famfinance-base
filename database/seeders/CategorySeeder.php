<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Gastos
            ['name' => 'Alimentación',      'icon' => 'shopping-cart',  'color' => '#F59E0B', 'type' => 'expense'],
            ['name' => 'Transporte',         'icon' => 'truck',          'color' => '#3B82F6', 'type' => 'expense'],
            ['name' => 'Salud',              'icon' => 'heart',          'color' => '#EF4444', 'type' => 'expense'],
            ['name' => 'Educación',          'icon' => 'book-open',      'color' => '#8B5CF6', 'type' => 'expense'],
            ['name' => 'Vivienda',           'icon' => 'home',           'color' => '#10B981', 'type' => 'expense'],
            ['name' => 'Servicios',          'icon' => 'lightning-bolt', 'color' => '#F97316', 'type' => 'expense'],
            ['name' => 'Entretenimiento',    'icon' => 'film',           'color' => '#EC4899', 'type' => 'expense'],
            ['name' => 'Ropa y calzado',     'icon' => 'tag',            'color' => '#06B6D4', 'type' => 'expense'],
            ['name' => 'Tecnología',         'icon' => 'desktop-computer','color' => '#6366F1', 'type' => 'expense'],
            ['name' => 'Restaurantes',       'icon' => 'fire',           'color' => '#D97706', 'type' => 'expense'],
            ['name' => 'Viajes',             'icon' => 'globe',          'color' => '#0EA5E9', 'type' => 'expense'],
            ['name' => 'Mascotas',           'icon' => 'emoji-happy',    'color' => '#84CC16', 'type' => 'expense'],
            ['name' => 'Impuestos',          'icon' => 'document-text',  'color' => '#6B7280', 'type' => 'expense'],
            ['name' => 'Seguros',            'icon' => 'shield-check',   'color' => '#14B8A6', 'type' => 'expense'],
            ['name' => 'Otros gastos',       'icon' => 'dots-horizontal','color' => '#9CA3AF', 'type' => 'expense'],

            // Ingresos
            ['name' => 'Sueldo',             'icon' => 'cash',           'color' => '#22C55E', 'type' => 'income'],
            ['name' => 'Freelance',          'icon' => 'briefcase',      'color' => '#16A34A', 'type' => 'income'],
            ['name' => 'Inversiones',        'icon' => 'trending-up',    'color' => '#15803D', 'type' => 'income'],
            ['name' => 'Alquiler cobrado',   'icon' => 'key',            'color' => '#166534', 'type' => 'income'],
            ['name' => 'Préstamo recibido',  'icon' => 'currency-dollar','color' => '#A3E635', 'type' => 'income'],
            ['name' => 'Otros ingresos',     'icon' => 'plus-circle',    'color' => '#86EFAC', 'type' => 'income'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'family_group_id' => null,
                'name'       => $cat['name'],
                'icon'       => $cat['icon'],
                'color'      => $cat['color'],
                'type'       => $cat['type'],
                'is_system'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
