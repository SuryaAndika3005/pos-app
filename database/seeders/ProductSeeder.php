<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'BSA-001', 'name' => 'Busa Super 5cm', 'category' => 'Busa',
                'description' => 'Density 20, Lembaran, Cocok untuk sofa standar.',
                'price' => 150000, 'cost_price' => 110000,
                'unit' => 'lbr', 'unit_type' => 'integer', 'stock' => 40, 'min_stock' => 10,
            ],
            [
                'sku' => 'KN-001', 'name' => 'Kain Oscar Sintetis', 'category' => 'Kain',
                'description' => 'Sintetis, Anti Air, Lebar 1.4 meter, Cokelat.',
                'price' => 45000, 'cost_price' => 30000,
                'unit' => 'mtr', 'unit_type' => 'decimal', 'stock' => 8, 'min_stock' => 10,
            ],
            [
                'sku' => 'DKR-001', 'name' => 'Dakron Silikon', 'category' => 'Dakron',
                'description' => 'Isian bantal premium, lembut & tahan lama.',
                'price' => 35000, 'cost_price' => 22000,
                'unit' => 'kg', 'unit_type' => 'decimal', 'stock' => 25, 'min_stock' => 5,
            ],
            [
                'sku' => 'BSA-002', 'name' => 'Busa Rebonded', 'category' => 'Busa',
                'description' => 'Padat, untuk alas duduk keras dan kasur.',
                'price' => 220000, 'cost_price' => 170000,
                'unit' => 'lbr', 'unit_type' => 'integer', 'stock' => 15, 'min_stock' => 5,
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['sku' => $p['sku']], $p);
        }
    }
}
