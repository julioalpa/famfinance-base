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
            ['name' => 'Alimentación',      'icon' => 'cart',       'color' => '#F59E0B', 'type' => 'expense'],
            ['name' => 'Transporte',         'icon' => 'car',        'color' => '#3B82F6', 'type' => 'expense'],
            ['name' => 'Salud',              'icon' => 'heart',      'color' => '#EF4444', 'type' => 'expense'],
            ['name' => 'Educación',          'icon' => 'book',       'color' => '#8B5CF6', 'type' => 'expense'],
            ['name' => 'Vivienda',           'icon' => 'home',       'color' => '#10B981', 'type' => 'expense'],
            ['name' => 'Servicios',          'icon' => 'bolt',       'color' => '#F97316', 'type' => 'expense'],
            ['name' => 'Entretenimiento',    'icon' => 'film',       'color' => '#EC4899', 'type' => 'expense'],
            ['name' => 'Ropa y calzado',     'icon' => 'shirt',      'color' => '#06B6D4', 'type' => 'expense'],
            ['name' => 'Tecnología',         'icon' => 'wifi',       'color' => '#6366F1', 'type' => 'expense'],
            ['name' => 'Restaurantes',       'icon' => 'restaurant', 'color' => '#D97706', 'type' => 'expense'],
            ['name' => 'Viajes',             'icon' => 'plane',      'color' => '#0EA5E9', 'type' => 'expense'],
            ['name' => 'Mascotas',           'icon' => 'paw',        'color' => '#84CC16', 'type' => 'expense'],
            ['name' => 'Impuestos',          'icon' => 'percent',    'color' => '#6B7280', 'type' => 'expense'],
            ['name' => 'Seguros',            'icon' => 'percent',    'color' => '#14B8A6', 'type' => 'expense'],
            ['name' => 'Otros gastos',       'icon' => 'other',      'color' => '#9CA3AF', 'type' => 'expense'],

            // Ingresos
            ['name' => 'Sueldo',             'icon' => 'dollar',     'color' => '#22C55E', 'type' => 'income'],
            ['name' => 'Freelance',          'icon' => 'briefcase',  'color' => '#16A34A', 'type' => 'income'],
            ['name' => 'Inversiones',        'icon' => 'chart',      'color' => '#15803D', 'type' => 'income'],
            ['name' => 'Alquiler cobrado',   'icon' => 'home',       'color' => '#166534', 'type' => 'income'],
            ['name' => 'Préstamo recibido',  'icon' => 'dollar',     'color' => '#A3E635', 'type' => 'income'],
            ['name' => 'Otros ingresos',     'icon' => 'other',      'color' => '#86EFAC', 'type' => 'income'],
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
