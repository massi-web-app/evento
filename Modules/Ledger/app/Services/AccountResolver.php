<?php

declare(strict_types=1);

namespace Modules\Ledger\Services;

use Modules\Ledger\Enums\AccountType;
use Modules\Ledger\Models\LedgerAccount;

final class AccountResolver
{
    public function platformCash(): LedgerAccount
    {
        return $this->firstOrCreate('platform:cash', AccountType::Asset);
    }

    public function commissionRevenue(): LedgerAccount
    {
        return $this->firstOrCreate('platform:commission_revenue', AccountType::Revenue);
    }

    public function serviceFeeRevenue(): LedgerAccount
    {
        return $this->firstOrCreate('platform:service_fee_revenue', AccountType::Revenue);
    }

    public function organizerPayable(int $organizerId): LedgerAccount
    {
        return LedgerAccount::query()->firstOrCreate(
            ['code' => "organizer:{$organizerId}:payable"],
            ['type' => AccountType::Liability, 'owner_type' => 'organizer', 'owner_id' => $organizerId],
        );
    }

    private function firstOrCreate(string $code, AccountType $type): LedgerAccount
    {
        return LedgerAccount::query()->firstOrCreate(
            ['code' => $code],
            ['type' => $type],
        );
    }
}
