<?php
declare(strict_types=1);
namespace Modules\Orders\Contracts;

use Modules\Orders\DTOs\PaidOrderSnapshot;

interface PaidOrderReader
{
    public function byPublicId(string $orderPublicId): PaidOrderSnapshot;

}
