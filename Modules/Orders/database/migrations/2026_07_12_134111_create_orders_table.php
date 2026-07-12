<?php

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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->foreignId('session_id')->constrained('event_sessions')->restrictOnDelete();

            $table->unsignedTinyInteger('status')->default(1)
                ->comment('1=pending 2=awaiting_payment 3=paid 4=canceled 5=expired 6=refunded 7=partially_refunded');


            $table->unsignedBigInteger('subtotal_amount');      // BIGINT ریال — قرارداد
            $table->unsignedBigInteger('service_fee_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total_amount');
            $table->char('currency', 3)->default('IRR');


            $table->dateTime('hold_expires_at')->nullable();    // مهلت پنجرهٔ رزرو
            $table->dateTime('paid_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'hold_expires_at']);
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->restrictOnDelete();

            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_amount_snapshot');       // 💎 قیمت فریزشده
            $table->string('ticket_type_name_snapshot', 100);         // 💎 حتی اسم — رسید باید ابدی باشد
            $table->unsignedBigInteger('line_total_amount');

            $table->timestamps();

            $table->unique(['order_id', 'ticket_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
