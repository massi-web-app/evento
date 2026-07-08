<?php

declare(strict_types=1);

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Identity\Enums\OrganizerStatus;
use Modules\Identity\Enums\OrganizerType;
use Modules\Identity\Enums\VerificationTier;
use Modules\Shared\Concerns\HasPublicId;

final class Organizer extends Model
{
    use HasPublicId;
    use SoftDeletes;

    protected $fillable = [
        'brand_name', 'slug', 'legal_name', 'bio',
        'logo_url', 'cover_url', 'social_links', 'type',
    ];

    protected $casts = [
        'type' => OrganizerType::class,
        'status' => OrganizerStatus::class,
        'verification_tier' => VerificationTier::class,
        'social_links' => 'array',
        'reputation_score' => 'decimal:2',
    ];

    protected $attributes = [
        'verification_tier' => 1,
        'reputation_score' => 0,
        'total_events' => 0,
        'default_currency' => 'IRR',
    ];

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return HasMany<OrganizerMember, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(OrganizerMember::class);
    }
}
