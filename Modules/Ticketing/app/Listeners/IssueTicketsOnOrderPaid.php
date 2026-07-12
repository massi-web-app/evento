<?php
declare(strict_types=1);

namespace Modules\Ticketing\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Modules\Orders\Events\OrderPaid;
use Modules\Ticketing\Services\TicketIssuanceService;

final class IssueTicketsOnOrderPaid implements ShouldQueueAfterCommit
{
    public string $queue = 'tickets';

    public function __construct(
        private readonly TicketIssuanceService $issuance,
    ) {}

    public function handle(OrderPaid $event): void
    {
        $this->issuance->issueForOrder($event->orderPublicId);
    }
}
