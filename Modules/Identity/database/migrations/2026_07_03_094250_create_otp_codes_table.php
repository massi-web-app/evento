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
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();

            $table->string('identifier');
            $table->unsignedTinyInteger('channel')
                ->comment('1=sms 2=email');

            $table->unsignedTinyInteger('purpose')
                ->comment('1=login 2=verify_phone 3=verify_email 4=reset_password 5=two_factor');

            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts');

            $table->dateTime('expires_at');
            $table->dateTime('consumed_at')->nullable();

            $table->timestamps();

            $table->index(['identifier', 'purpose', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
