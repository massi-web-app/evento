<?php
declare(strict_types=1);

namespace Modules\Ledger\Listeners;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Modules\Ledger\DTOs\EntryLine;
use Modules\Ledger\Services\AccountResolver;
use Modules\Ledger\Services\LedgerService;
use Modules\Orders\Contracts\PaidOrderReader;
use Modules\Orders\Events\PaymentVerified;
use Modules\Shared\ValueObjects\Money;

final   class RecordPaymentInLedger implements ShouldQueueAfterCommit
{

    public string $queue = 'ledger';


    public function __construct(
        private readonly LedgerService   $ledger,
        private readonly AccountResolver $accounts,
        private readonly PaidOrderReader $orders
    )
    {
    }

    public function handle(PaymentVErified $event): void
    {
        $snapshot = $this->orders->byPublicId($event->orderPublicId);

        $total = Money::irr($event->amount);

        $cash = $this->accounts->platformCash();
        $payable = $this->accounts->organizerPayable($snapshot->organizerId);

        $this->ledger->record(
            sourceType: 'payment_verified',
            sourceId: $event->paymentPublicId,
            description: "Payment for order {$event->orderPublicId} (ref {$event->gatewayRef})",
            occurredAt: CarbonImmutable::now(),
            lines: [
                EntryLine::debit($cash->code, $total),
                EntryLine::credit($payable->code, $total),
            ],
        );

    }


}
