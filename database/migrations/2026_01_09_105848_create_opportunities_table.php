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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('public_id')->unique();          // e.g. opportunity_001
            $table->string('apprenticeship_id')->unique();  // e.g. APP-48219

            $table->unsignedBigInteger('user_id');          // posted_by

            $table->json('skills_needed');                  // ["Carpentry", ...]
            $table->date('apprenticeship_start_date')->nullable();
            $table->unsignedInteger('duration_weeks')->nullable();

            $table->boolean('compensation_paid')->default(false);
            $table->decimal('total_pay_offering', 10, 2)->nullable();

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('city')->nullable();

            $table->string('title')->nullable();            // e.g. "Carpenter"
            $table->text('apprenticeship_description')->nullable();

            $table->timestamps();
            $table->softDeletes();                          // deleted_at for delete API. 

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
