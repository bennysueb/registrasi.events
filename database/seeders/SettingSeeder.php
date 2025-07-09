<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('setting')->insert([
            'name_app' => 'e-invitation',
            'color_bg_app' => '#006831',
            'send_whatsapp' => 1,
        ]);
    }
}
