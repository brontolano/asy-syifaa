<?php

namespace Database\Seeders;

use App\Models\CmsCategory;
use Illuminate\Database\Seeder;

class CmsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'article', 'name' => 'Artikel', 'type' => 'article', 'description' => 'Artikel umum website', 'sort_order' => 1],
            ['slug' => 'announcement', 'name' => 'Info & Pengumuman', 'type' => 'announcement', 'description' => 'Informasi dan pengumuman pesantren', 'sort_order' => 2],
            ['slug' => 'achievement', 'name' => 'Prestasi', 'type' => 'achievement', 'description' => 'Prestasi santri dan pesantren', 'sort_order' => 3],
            ['slug' => 'gallery', 'name' => 'Galeri Foto', 'type' => 'gallery', 'description' => 'Dokumentasi kegiatan pesantren', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            CmsCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
