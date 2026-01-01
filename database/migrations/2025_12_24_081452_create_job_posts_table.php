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

        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            
            $table->string('title')->nullable();
            $table->string('company_name')->nullable();

            // Public job identifier used in APIs, e.g. "JOB12345"
            $table->string('job_code')->unique();

            // Owner of the job (contractor user)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Link to specializations table
            $table->unsignedBigInteger('specialization_id');
            $table->foreign('specialization_id')
                ->references('id')
                ->on('specializations')
                ->onDelete('restrict');

            // Job scheduling
            $table->date('start_date')->nullable();
            $table->unsignedInteger('duration_value')->nullable(); // e.g. 3
            $table->string('duration_unit')->nullable();           // e.g. "days", "weeks", "months"

            // Pay rate (amount + currency + type)
            $table->decimal('pay_rate_amount', 8, 2)->nullable();  // e.g. 35.50
            $table->string('pay_rate_currency', 3)->nullable();    // e.g. "USD"
            $table->string('pay_rate_type', 20)->nullable();       // e.g. "hour", "day"

            // Location
            $table->decimal('location_lat', 10, 7)->nullable();    // latitude
            $table->decimal('location_lng', 10, 7)->nullable();    // longitude
            $table->string('city')->nullable();                    // e.g. "Downtown"

            // Description
            $table->text('job_description')->nullable();

            // Flags and status
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('pending');          // e.g. "pending", "active", "closed"

            $table->timestamps();
        });

        Schema::create('job_post_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->onDelete('cascade');
            $table->foreignId('skill_id')->constrained('skills')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_post_skill');
        Schema::dropIfExists('job_posts');
    }
};
