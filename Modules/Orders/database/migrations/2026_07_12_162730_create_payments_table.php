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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();

            $table->string('gateway', 50);                       // 'fake', 'zarinpal', ...
            $table->unsignedTinyInteger('status')->default(1)
                ->comment('1=initiated 2=redirected 3=verified 4=failed 5=reversed');

            $table->unsignedBigInteger('amount');                // BIGINT ریال
            $table->char('currency', 3)->default('IRR');

            $table->string('gateway_token', 191)->nullable();    // authority/token درگاه
            $table->string('gateway_ref', 191)->nullable();      // ref_id بعد از verify
            $table->json('gateway_meta')->nullable();            // پاسخ خام — برای فردا روز اختلاف

            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->unique('gateway_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
