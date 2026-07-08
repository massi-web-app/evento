<?php

declare(strict_types=1);

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Enums\SettingType;
use Modules\Settings\Models\SettingDefinition;

final class SettingDefinitionsSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            // group, key, type, default, overridable, description
            ['otp', 'otp.expiry_seconds', SettingType::Integer, '120', false, 'مهلت اعتبار کد OTP'],
            ['otp', 'otp.max_attempts', SettingType::Integer, '5', false, 'سقف تلاش verify هر کد'],
            ['otp', 'otp.send_max_per_window', SettingType::Integer, '3', false, 'سقف ارسال در پنجره'],
            ['otp', 'otp.send_window_seconds', SettingType::Integer, '600', false, 'طول پنجرهٔ ارسال'],
            ['commission', 'commission.default_rate', SettingType::Decimal, '12.00', true, 'نرخ کمیسیون پایه ٪'],
            ['commission', 'commission.service_fee_percent', SettingType::Decimal, '3.00', true, 'کارمزد خدمات خریدار ٪'],
            ['credits', 'credits.weekly_free_amount', SettingType::Integer, '2', true, 'اعتبار رایگان هفتگی'],
            ['escrow', 'escrow.auto_release_days', SettingType::Integer, '3', false, 'آزادسازی خودکار escrow'],
            ['order', 'order.hold_expiry_minutes', SettingType::Integer, '15', false, 'مهلت hold سفارش'],
            ['session', 'session.lifetime_minutes', SettingType::Integer, '20160', false, 'عمر session'],
        ];

        foreach ($definitions as [$group, $key, $type, $default, $overridable, $description]) {
            SettingDefinition::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group_name' => $group,
                    'value_type' => $type,
                    'default_value' => $default,
                    'is_overridable' => $overridable,
                    'description' => $description,
                ],
            );
        }
    }
}
