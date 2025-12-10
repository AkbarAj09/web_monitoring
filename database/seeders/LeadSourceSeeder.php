<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            'Data Sendiri',
            'OBC Leads',
            'SMS blast leads',
            'Enterprise Leads',
            'UMKM',
            'Top600',
            'WhatsApp',
            'Meta Leads',
            'Tiktok Leads',
            'Online Webinar MyAds',
            'Data Top 10 AAM',
            'Register Day/Day',
            'Webinar Growlab',
            'CERMAD Roadshow',
            'B2B',
            'Webinar SATMESIN x Fine Creative',
            "15% Juli - Haven't top up recently",
            "20% Juli - Havent top up recently",
            '30% Juli - First top up',
            '25% Juli - Regular',
            '20% Juli - Reguler',
            '15% Juli - Reguler',
            'Data Outlet Digipos',
            'Data complaint CS',
            'Dormant',
            'PaDi UMKM',
        ];

        foreach ($items as $name) {
            DB::table('leads_source')->insert([
                'name' => $name,
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
