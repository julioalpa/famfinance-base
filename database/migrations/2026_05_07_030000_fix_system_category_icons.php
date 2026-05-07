<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $fixes = [
            'Alimentación'     => 'cart',
            'Transporte'       => 'car',
            'Educación'        => 'book',
            'Servicios'        => 'bolt',
            'Ropa y calzado'   => 'shirt',
            'Tecnología'       => 'wifi',
            'Restaurantes'     => 'restaurant',
            'Viajes'           => 'plane',
            'Mascotas'         => 'paw',
            'Impuestos'        => 'percent',
            'Seguros'          => 'percent',
            'Otros gastos'     => 'other',
            'Sueldo'           => 'dollar',
            'Inversiones'      => 'chart',
            'Alquiler cobrado' => 'home',
            'Préstamo recibido'=> 'dollar',
            'Otros ingresos'   => 'other',
        ];

        foreach ($fixes as $name => $icon) {
            DB::table('categories')
                ->where('name', $name)
                ->where('is_system', true)
                ->update(['icon' => $icon]);
        }
    }

    public function down(): void
    {
        $originals = [
            'Alimentación'     => 'shopping-cart',
            'Transporte'       => 'truck',
            'Educación'        => 'book-open',
            'Servicios'        => 'lightning-bolt',
            'Ropa y calzado'   => 'tag',
            'Tecnología'       => 'desktop-computer',
            'Restaurantes'     => 'fire',
            'Viajes'           => 'globe',
            'Mascotas'         => 'emoji-happy',
            'Impuestos'        => 'document-text',
            'Seguros'          => 'shield-check',
            'Otros gastos'     => 'dots-horizontal',
            'Sueldo'           => 'cash',
            'Inversiones'      => 'trending-up',
            'Alquiler cobrado' => 'key',
            'Préstamo recibido'=> 'currency-dollar',
            'Otros ingresos'   => 'plus-circle',
        ];

        foreach ($originals as $name => $icon) {
            DB::table('categories')
                ->where('name', $name)
                ->where('is_system', true)
                ->update(['icon' => $icon]);
        }
    }
};
