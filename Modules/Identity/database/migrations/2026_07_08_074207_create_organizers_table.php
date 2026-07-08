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
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('type')
                ->comment('1=individual 2=business');
            $table->string('brand_name', 150);
            $table->string('slug', 160)->unique();
            $table->string('legal_name')->nullable();
            $table->text('bio')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->json('social_links')->nullable();

            $table->unsignedTinyInteger('status')
                ->comment('1=pending 2=active 3=suspended 4=rejected');
            $table->unsignedTinyInteger('verification_tier')->default(1)
                ->comment('1=none 2=bronze 3=silver 4=gold');   // enum با مقدار خالی '' اصلاح شد ✅

            $table->decimal('reputation_score', 6, 2)->default(0);   // منبع: M12
            $table->unsignedInteger('total_events')->default(0);     // varchar(255) → INT ✅ منبع: M4
            $table->char('default_currency', 3)->default('IRR');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('organizer_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('role')
                ->comment('1=owner 2=admin 3=manager 4=checkin_staff 5=marketer');
            $table->unsignedTinyInteger('status')->default(1)
                ->comment('1=invited 2=active 3=suspended 4=removed');
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['organizer_id', 'user_id']);     // PK چهارستونهٔ Navicat → id + UNIQUE درست ✅
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_members');
        Schema::dropIfExists('organizers');
    }
};
