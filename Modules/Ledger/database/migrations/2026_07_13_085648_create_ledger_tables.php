<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 100)->unique();          // 'platform:cash' یا 'organizer:42:payable'
            $table->unsignedTinyInteger('type')
                ->comment('1=asset 2=liability 3=revenue 4=expense');
            $table->string('owner_type', 50)->nullable();   // 'organizer' | null برای حساب‌های پلتفرم
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->char('currency', 3)->default('IRR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id', 'type']);
        });

        Schema::create('ledger_transactions', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('source_type', 100);             // 'payment_verified'
            $table->string('source_id', 100);               // public_id پرداخت
            $table->string('description');
            $table->dateTime('occurred_at');
            $table->timestamp('created_at')->nullable();    // فقط created — immutable است، updated معنا ندارد

            $table->unique(['source_type', 'source_id']);   // 🔒 idempotency در سطح schema
        });

        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->constrained('ledger_transactions')->restrictOnDelete();
            $table->foreignId('account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->unsignedTinyInteger('direction')->comment('1=debit 2=credit');
            $table->unsignedBigInteger('amount');           // همیشه مثبت — جهت در direction
            $table->timestamp('created_at')->nullable();

            $table->index(['account_id', 'transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('ledger_accounts');
    }
};
