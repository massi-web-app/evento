<?php

declare(strict_types=1);

namespace Modules\Orders\Console;

use Illuminate\Console\Command;
use Modules\Orders\Services\HoldExpiryService;

final class ExpireOverdueHoldsCommand extends Command
{
    protected $signature = 'orders:expire-holds';

    protected $description = 'Expire overdue order holds and release capacity back to counters';

    public function handle(HoldExpiryService $service): int
    {
        $count = $service->expireOverdue();

        $this->info("Expired {$count} overdue hold(s).");

        return self::SUCCESS;
    }

}
