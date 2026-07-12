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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('order_item_id')->constrained('order_items')->restrictOnDelete();
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->foreignId('session_id')->constrained('event_sessions')->restrictOnDelete();
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->restrictOnDelete();
            $table->foreignId('holder_user_id')->constrained('users')->restrictOnDelete();

            $table->unsignedTinyInteger('status')->default(1)
                ->comment('1=issued 2=checked_in 3=canceled 4=refunded 5=transferred');

            $table->char('checkin_code', 32)->unique();
            $table->dateTime('issued_at');
            $table->dateTime('checked_in_at')->nullable();

            $table->timestamps();

            $table->index(['session_id', 'status']);       // لیست گیت check-in
            $table->index(['holder_user_id', 'status']);   // «بلیت‌های من»
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
