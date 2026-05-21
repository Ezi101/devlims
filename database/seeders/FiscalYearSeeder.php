<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\FiscalYear;
use Carbon\Carbon;

class FiscalYearSeeder extends Seeder
{
    public function run()
    {
        $fiscalYears = [
            [
                'name' => 'FY 2022-2023',
                'start_date' => '2022-07-01',
                'end_date' => '2023-06-30',
                'start_year' => 2022,
                'end_year' => 2023,
                'is_active' => false,
            ],
            [
                'name' => 'FY 2023-2024',
                'start_date' => '2023-07-01',
                'end_date' => '2024-06-30',
                'start_year' => 2023,
                'end_year' => 2024,
                'is_active' => true,
            ],
            [
                'name' => 'FY 2024-2025',
                'start_date' => '2024-07-01',
                'end_date' => '2025-06-30',
                'start_year' => 2024,
                'end_year' => 2025,
                'is_active' => false,
            ],
        ];

        foreach ($fiscalYears as $fiscalYear) {
            FiscalYear::create($fiscalYear);
        }
    }
}