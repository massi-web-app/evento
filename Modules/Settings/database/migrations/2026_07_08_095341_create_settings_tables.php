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

        Schema::create('setting_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150)->unique();
            $table->unsignedTinyInteger('value_type')
                ->comment('1=int 2=decimal 3=string 4=bool 5=json');
            $table->text('default_value');

            $table->string('group_name', 100)->index();
            $table->string('description')->nullable();
            $table->boolean('is_overridable')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('definition_id')->constrained('setting_definitions')->cascadeOnDelete();
            $table->string('scope_type', 50)->default('global');   // global | organizer | plan | ...
            $table->unsignedBigInteger('scope_id')->nullable();    // polymorphic — بدون FK (قرارداد master)
            $table->text('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['definition_id', 'scope_type', 'scope_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('setting_definitions');
    }
};
