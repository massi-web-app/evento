<?php
declare(strict_types=1);

namespace Modules\Shared\Concerns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * موجودیت‌های public-facing یک ULID در ستون public_id دارند.
 *
 * @property string $public_id
 */
trait HasPublicId
{
    public static function bootHasPublicId(): void
    {
        static::creating(function (self $model): void {
            $model->public_id ??= (string) Str::ulid();
        });

    }
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

}
