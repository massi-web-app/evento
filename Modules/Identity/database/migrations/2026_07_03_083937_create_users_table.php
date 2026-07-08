<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Identity\Enums\UserStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26);

            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('display_name', 150)->nullable();

            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password')->nullable();

            $table->string('avatar_url')->nullable();

            $table->unsignedTinyInteger('status')
                ->default(UserStatus::Pending->value)
                ->comment('1=pending 2=active 3=suspended 4=banned');

            $table->string('locale', 10)->default('fa');
            $table->string('timezone', 64)->default('Asia/Tehran');

            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('is_staff')->default(false);

            $table->timestamp('last_login_at')->nullable();
            $table->binary('last_login_ip', 16)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
