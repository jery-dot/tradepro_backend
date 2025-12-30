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
        Schema::create('laborers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->string('custom_specialization')->nullable();
            $table->string('experience_level')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->boolean('has_insurance')->default(false);
            $table->boolean('background_check_completed')->default(false);
            $table->boolean('looking_for_apprenticeship')->default(false);
            $table->string('trade_school_name')->nullable();
            $table->string('trade_school_program_year')->nullable();
            $table->boolean('profile_completion')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laborers');
    }
};
