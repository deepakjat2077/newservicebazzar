<?php

namespace App\Console\Commands;

use App\Support\SalesPerks;
use Illuminate\Console\Command;

class ExpireSalesPerks extends Command
{
    protected $signature = 'sales:expire-perks';

    protected $description = 'Take back verified/trust badges whose paid plans have run out';

    public function handle(): int
    {
        $this->info(SalesPerks::expire() . ' listing(s) reverted.');

        return self::SUCCESS;
    }
}
