<?php


declare(strict_types=1);

namespace Modules\Identity\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Identity\Database\Factories\UserFactory;
use Modules\Identity\Enums\UserStatus;
use Modules\Shared\Concerns\HasPublicId;

final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasPublicId;

    protected $fillable = [
        'first_name',
        'last_name',
        'display_name',
        'email',
        'phone',
        'password',
        'avatar_url',
        'locale',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'last_login_ip',
    ];

    protected $casts = [
        'status' => UserStatus::class,
        'email_verified_at' => 'immutable_datetime',
        'phone_verified_at' => 'immutable_datetime',
        'last_login_at' => 'immutable_datetime',
        'two_factor_enabled' => 'boolean',
        'is_staff' => 'boolean',
        'password' => 'hashed',
    ];



    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

}
