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
        Schema::create('apprentice_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('public_id')->unique(); // e.g. apprentice_001

            $table->unsignedBigInteger('user_id'); // owner (labor/apprentice user)

            $table->string('position_seeking');
            $table->unsignedTinyInteger('age')->nullable();

            // store both structured coordinates + human-readable location
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('city')->nullable();     // e.g. "Rjkot"
            $table->string('location_text')->nullable(); // e.g. "Austin, USA"

            $table->string('education_experience')->nullable();
            $table->string('trade_school')->nullable();
            $table->text('about_me')->nullable();

            $table->string('resume_file_url')->nullable();
            $table->boolean('profile_visible')->default(true);

            $table->timestamps();
            $table->softDeletes(); // deleted_at column for soft delete APIs. 

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apprentice_profiles');
    }
};
