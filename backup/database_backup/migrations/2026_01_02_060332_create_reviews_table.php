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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // Public ID like "review_456789" can be stored in this code column
            $table->string('review_code')->unique();

            // Relations
            $table->unsignedBigInteger('job_post_id');   // FK to job_posts
            $table->unsignedBigInteger('reviewer_id');   // FK to users (reviewer)
            $table->unsignedBigInteger('reviewee_id')->nullable(); // person being reviewed (optional)

            $table->unsignedTinyInteger('overall_rating'); // 1–5

            $table->string('recommendation')->nullable();  // e.g. "recommended", "not_recommended"

            // Individual ratings
            $table->decimal('communication_rating', 3, 1)->nullable();
            $table->decimal('job_quality_rating', 3, 1)->nullable();
            $table->decimal('professionalism_rating', 3, 1)->nullable();

            $table->boolean('job_complete_satisfaction')->default(false);

            $table->text('comment')->nullable();

            // Cached average rating (for quick reads)
            $table->decimal('average_rating', 3, 1)->nullable();

            $table->timestamps();

            $table->foreign('job_post_id')->references('id')->on('job_posts')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
