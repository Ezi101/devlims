<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contract;
use Carbon\Carbon;

class ParseContractFiscalData extends Command
{
    protected $signature = 'contracts:parse-fiscal';
    protected $description = 'Parse entry_date text field and extract fiscal years and dates';

    public function handle()
    {
        $contracts = Contract::whereNotNull('entry_date')->get();

        foreach ($contracts as $contract) {
            $text = $contract->entry_date;

            // Example formats:
            // "2024-2025 01-03-2024 to 01-03-2025"
            // "Fiscal Year 2025 to 2026 01-04-2025 to 01-04-2026"

            preg_match('/(\d{4})\D+(\d{4})/', $text, $yearMatches);
            preg_match_all('/(\d{2}-\d{2}-\d{4})/', $text, $dateMatches);

            $startYear = $yearMatches[1] ?? null;
            $endYear   = $yearMatches[2] ?? null;

            $startDate = isset($dateMatches[1][0]) ? Carbon::createFromFormat('d-m-Y', $dateMatches[1][0]) : null;
            $endDate   = isset($dateMatches[1][1]) ? Carbon::createFromFormat('d-m-Y', $dateMatches[1][1]) : null;

            $contract->update([
                'fiscal_start_year' => $startYear,
                'fiscal_end_year'   => $endYear,
                'fiscal_start_date' => $startDate,
                'fiscal_end_date'   => $endDate,
            ]);

            $this->info("Parsed contract #{$contract->id}: {$startYear}-{$endYear}");
        }

        $this->info('All contracts parsed successfully!');
    }
}
