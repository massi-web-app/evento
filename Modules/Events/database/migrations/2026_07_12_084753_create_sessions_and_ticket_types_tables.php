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
        Schema::create('event_sessions', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('title', 150)->nullable();        // «اجرای شب اول» — nullable برای تک‌اجرا
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('capacity')->nullable(); // سقف اختصاصی session، جدا از سقف event
            $table->unsignedTinyInteger('status')->default(1)
                ->comment('1=scheduled 2=canceled 3=completed');
            $table->timestamps();

            $table->index(['event_id', 'starts_at']);
        });

        Schema::create('ticket_types', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('session_id')->constrained('event_sessions')->cascadeOnDelete();
            $table->string('name', 100);                     // VIP / عادی / دانشجویی
            $table->string('description')->nullable();
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('sold_cache')->default(0);
            $table->unsignedInteger('min_per_order')->default(1);
            $table->unsignedInteger('max_per_order')->default(10);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['session_id', 'is_active', 'sort_order']);
        });

        Schema::create('ticket_type_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->cascadeOnDelete();
            $table->string('label', 100)->nullable();        // «پیش‌فروش» — برای UI
            $table->unsignedBigInteger('amount');            // ریال — قرارداد Money
            $table->char('currency', 3)->default('IRR');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();         // null = تا اطلاع ثانوی/سقف session
            $table->timestamps();

            $table->index(['ticket_type_id', 'starts_at']);
        });



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_type_prices');
        Schema::dropIfExists('ticket_types');
        Schema::dropIfExists('event_sessions');
    }
};
