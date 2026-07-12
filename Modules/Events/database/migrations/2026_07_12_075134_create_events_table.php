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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('organizer_id')->constrained('organizers')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained('venues')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->restrictOnDelete();


            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('cover_url')->nullable();


            $table->unsignedTinyInteger('format')
                ->comment('1=in_person 2=online 3=hybrid');
            $table->unsignedTinyInteger('status')->default(1)
                ->comment('1=draft 2=pending_review 3=approved 4=published 5=paused 6=ended 7=canceled 8=rejected');

            $table->dateTime('starts_at')->nullable();        // زمان آینده → DATETIME UTC
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('published_at')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('capacity_total')->nullable();

            // ---- کش‌های denormalized (منبع حقیقت: M5/M12) ----
            $table->unsignedInteger('tickets_sold_cache')->default(0);
            $table->decimal('rating_cache', 3, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'starts_at']);
            $table->index(['city_id', 'status']);
            $table->index(['organizer_id', 'status']);
            $table->index(['category_id', 'status']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
