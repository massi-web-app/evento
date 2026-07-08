<?php

declare(strict_types=1);

namespace Modules\Identity\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\Role;

final class RbacSeeder extends Seeder
{

    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'مدیر پلتفرم'],
            ['name' => 'support', 'display_name' => 'پشتیبانی'],
            ['name' => 'organizer', 'display_name' => 'سازندهٔ رویداد'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['name' => $role['name']],
                [...$role, 'is_system' => true],
            );
        }
    }

}
