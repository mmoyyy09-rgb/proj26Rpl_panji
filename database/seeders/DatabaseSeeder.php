<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {


        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin'
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User Biasa',
                'password' => Hash::make('user123'),
                'role' => 'user'
            ]
        );


        $categoriesData = [
            ['name' => 'Aksesoris Gadget', 'description' => 'Powerbank, kabel charger, adaptor, dan casing.', 'status' => 'Aktif'],
            ['name' => 'Audio & Speaker', 'description' => 'Headset, TWS, earphone, dan bluetooth speaker.', 'status' => 'Aktif'],
            ['name' => 'Gaming Gear', 'description' => 'Keyboard mechanical, mouse gaming, dan gamepad.', 'status' => 'Aktif'],
            ['name' => 'Kamera & Drone', 'description' => 'Kamera digital, action cam, dan drone profesional.', 'status' => 'Aktif'],
            ['name' => 'Laptop & Komputer', 'description' => 'Perangkat laptop, PC desktop, dan aksesoris komputer.', 'status' => 'Aktif'],
            ['name' => 'Smartphone', 'description' => 'Kumpulan HP dan smartphone berbagai brand.', 'status' => 'Aktif'],
            ['name' => 'Smartwatch', 'description' => 'Jam tangan pintar dan smartwatch kesehatan.', 'status' => 'Aktif'],
        ];

        // 1. Jumlah produk per kategori (Total = 500 produk) -> Grafik Atas
        $productCounts = [110, 55, 70, 35, 85, 130, 15];
        
        // 2. Target total stok per kategori (Total pas 10,000) -> Dibuat KONTRAS dengan jumlah produk
        // Contoh: Audio & Speaker produknya sedikit (55), tapi total stoknya tinggi (2,500)
        $targetCategoryStocks = [
            1200, // Aksesoris Gadget
            2500, // Audio & Speaker (Lonjakan stok tinggi!)
            900,  // Gaming Gear
            1600, // Kamera & Drone (Produk sedikit, tapi stok tinggi!)
            1000, // Laptop & Komputer
            2000, // Smartphone
            800   // Smartwatch
        ]; 
        // Total targetCategoryStocks = 10,000

        $totalProducts = 500;
        
        // Tentukan tepat 15 indeks produk secara acak global untuk stok menipis (1 - 5 unit)
        $allIndices = range(0, $totalProducts - 1);
        shuffle($allIndices);
        $lowStockGlobalIndices = array_slice($allIndices, 0, 15);

        $globalIndex = 0;
        $globalStocks = array_fill(0, $totalProducts, 0);

        // Distribusikan stok per kategori berdasarkan target kontras
        foreach ($productCounts as $catIndex => $count) {
            $catTargetStock = $targetCategoryStocks[$catIndex];
            $catCurrentStock = 0;
            $catIndices = range($globalIndex, $globalIndex + $count - 1);
            
            foreach ($catIndices as $i => $idx) {
                if (in_array($idx, $lowStockGlobalIndices)) {
                    $stock = rand(1, 5);
                    $globalStocks[$idx] = $stock;
                    $catCurrentStock += $stock;
                } else {
                    $isLastInCat = ($i === $count - 1);
                    if ($isLastInCat) {
                        $stock = max(6, $catTargetStock - $catCurrentStock);
                        $globalStocks[$idx] = $stock;
                        $catCurrentStock += $stock;
                    } else {
                        $remainingItemsInCat = $count - $i;
                        $remainingTargetStock = $catTargetStock - $catCurrentStock;
                        
                        $avgNeeded = $remainingTargetStock / $remainingItemsInCat;
                        $minVal = max(6, (int)($avgNeeded * 0.4));
                        $maxVal = max($minVal + 1, (int)($avgNeeded * 1.6));
                        $maxVal = min(70, $maxVal);
                        
                        if ($minVal > $maxVal) $minVal = $maxVal;

                        $stock = rand($minVal, $maxVal);
                        $globalStocks[$idx] = $stock;
                        $catCurrentStock += $stock;
                    }
                }
            }
            $globalIndex += $count;
        }

        // Penyesuaian presisi jika ada selisih pembulatan agar mutlak 10,000
        $currentTotal = array_sum($globalStocks);
        $diff = 10000 - $currentTotal;
        if ($diff != 0) {
            for ($i = 0; $i < $totalProducts; $i++) {
                if (!in_array($i, $lowStockGlobalIndices) && ($globalStocks[$i] + $diff) >= 6) {
                    $globalStocks[$i] += $diff;
                    break;
                }
            }
        }

        // Masukkan data ke database
        $globalIndex = 0;
        foreach ($categoriesData as $index => $catData) {
            $category = Category::updateOrCreate(
                ['name' => $catData['name']],
                $catData
            );
            $countForThisCategory = $productCounts[$index];

            for ($i = 1; $i <= $countForThisCategory; $i++) {
                $stockValue = $globalStocks[$globalIndex];
                $productCode = 'PRD-' . str_pad($globalIndex + 1, 4, '0', STR_PAD_LEFT);

                Product::updateOrCreate([
                    'product_code' => $productCode,
                ], [
                    'category_id' => $category->id,
                    'name' => $category->name . ' Series ' . $i,
                    'brand' => 'Brand ' . rand(1, 12),
                    'price' => rand(250, 15000) * 1000,
                    'stock' => $stockValue,
                    'description' => 'Deskripsi spesifikasi untuk produk ' . $category->name,
                ]);

                $globalIndex++;
            }
        }
    }
}