<?php

declare(strict_types=1);

namespace Modules\Ledger\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Modules\Ledger\DTOs\EntryLine;
use Modules\Ledger\Enums\EntryDirection;
use Modules\Ledger\Exceptions\UnbalancedTransactionException;
use Modules\Ledger\Models\LedgerAccount;
use Modules\Ledger\Models\LedgerTransaction;

final class LedgerService
{
    /**
     * ثبت یک تراکنش balanced و idempotent.
     *
     * @param list<EntryLine> $lines
     * @throws UnbalancedTransactionException
     */
    public function record(
        string $sourceType,
        string $sourceId,
        string $description,
        CarbonImmutable $occurredAt,
        array $lines,
    ): LedgerTransaction {
        $this->assertBalanced($lines);

        try {
            return DB::transaction(function () use ($sourceType, $sourceId, $description, $occurredAt, $lines): LedgerTransaction {
                $transaction = LedgerTransaction::query()->create([
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'description' => $description,
                    'occurred_at' => $occurredAt,
                ]);

                $accountIds = LedgerAccount::query()
                    ->whereIn('code', array_map(fn (EntryLine $l) => $l->accountCode, $lines))
                    ->pluck('id', 'code');

                foreach ($lines as $line) {
                    $transaction->entries()->create([
                        'account_id' => $accountIds[$line->accountCode]
                            ?? throw new \LogicException("Unknown ledger account [{$line->accountCode}] — resolve it first."),
                        'direction' => $line->direction,
                        'amount' => $line->amount,
                    ]);
                }

                return $transaction;
            });
        } catch (UniqueConstraintViolationException) {
            // تحویل دوم صف — تراکنش قبلاً ثبت شده؛ همان را برگردان (idempotency)
            return LedgerTransaction::query()
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->firstOrFail();
        }
    }

    /** تراز یک حساب: مثبت = در جهت طبیعی حساب. */
    public function balanceOf(string $accountCode): int
    {
        /** @var LedgerAccount $account */
        $account = LedgerAccount::query()->where('code', $accountCode)->firstOrFail();

        $sums = DB::table('ledger_entries')
            ->where('account_id', $account->id)
            ->selectRaw('direction, COALESCE(SUM(amount), 0) as total')
            ->groupBy('direction')
            ->pluck('total', 'direction');

        $debits = (int) ($sums[EntryDirection::Debit->value] ?? 0);
        $credits = (int) ($sums[EntryDirection::Credit->value] ?? 0);

        return $account->type->increasesWith() === EntryDirection::Debit
            ? $debits - $credits
            : $credits - $debits;
    }

    /** جمع کل بدهکار منهای بستانکار کل دفتر — همیشه باید صفر باشد. */
    public function trialBalance(): int
    {
        $sums = DB::table('ledger_entries')
            ->selectRaw('direction, COALESCE(SUM(amount), 0) as total')
            ->groupBy('direction')
            ->pluck('total', 'direction');

        return (int) ($sums[EntryDirection::Debit->value] ?? 0)
            - (int) ($sums[EntryDirection::Credit->value] ?? 0);
    }

    /** @param list<EntryLine> $lines */
    private function assertBalanced(array $lines): void
    {
        $debits = 0;
        $credits = 0;

        foreach ($lines as $line) {
            match ($line->direction) {
                EntryDirection::Debit => $debits += $line->amount->amount,
                EntryDirection::Credit => $credits += $line->amount->amount,
            };
        }

        if ($debits !== $credits || $debits === 0) {
            throw UnbalancedTransactionException::withSums($debits, $credits);
        }
    }
}
