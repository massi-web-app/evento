<?php

declare(strict_types=1);

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Identity\Enums\MemberRole;
use Modules\Identity\Enums\MemberStatus;

final class OrganizerMember extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'status',
        'invited_by',
        'invited_at',
        'joined_at',
    ];

    protected $casts = [
        'role' => MemberRole::class,
        'status' => MemberStatus::class,
        'invited_at' => 'immutable_datetime',
        'joined_at' => 'immutable_datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
