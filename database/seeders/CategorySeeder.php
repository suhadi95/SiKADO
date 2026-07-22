<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pendidikan dan Pengajaran', 'color' => '#2563EB', 'sort_order' => 1],
            ['name' => 'Penelitian', 'color' => '#7C3AED', 'sort_order' => 2],
            ['name' => 'Pengabdian kepada Masyarakat', 'color' => '#059669', 'sort_order' => 3],
            ['name' => 'Penunjang', 'color' => '#D97706', 'sort_order' => 4],
            ['name' => 'Tugas Tambahan', 'color' => '#DC2626', 'sort_order' => 5],
            ['name' => 'Kegiatan Lainnya', 'color' => '#64748B', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                [
                    'color' => $category['color'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
