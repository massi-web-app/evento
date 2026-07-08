<?php


declare(strict_types=1);

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
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
    use HasApiTokens;

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


    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot(['assigned_by', 'assigned_at']);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->roles
            ->loadMissing('permissions')
            ->pluck('permissions')
            ->flatten()
            ->contains('name', $permissionName);
    }

}
