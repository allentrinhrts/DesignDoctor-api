<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnalysisTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('analysis_types')->insert([
            ['name' => 'Image Analysis'],
            ['name' => 'Web Analysis'],
        ]);
    }
}
