<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ Add FK for reviewee_id
            |--------------------------------------------------------------------------
            */
            $table->foreign('reviewee_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ Prevent duplicate review per job per user
            |--------------------------------------------------------------------------
            */
            $table->unique(['job_post_id', 'reviewer_id'], 'unique_review_per_job');

            /*
            |--------------------------------------------------------------------------
            | 3️⃣ Add indexes for performance
            |--------------------------------------------------------------------------
            */
            $table->index('job_post_id');
            $table->index('reviewer_id');
            $table->index('reviewee_id');

            /*
            |--------------------------------------------------------------------------
            | 4️⃣ (Optional) Track review type
            |--------------------------------------------------------------------------
            */
            $table->enum('review_type', ['contractor_to_labor', 'labor_to_contractor'])
                ->nullable()
                ->after('reviewee_id');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {

            // Drop FK
            $table->dropForeign(['reviewee_id']);

            // Drop unique constraint
            $table->dropUnique('unique_review_per_job');

            // Drop indexes
            $table->dropIndex(['job_post_id']);
            $table->dropIndex(['reviewer_id']);
            $table->dropIndex(['reviewee_id']);

            // Drop column
            $table->dropColumn('review_type');
        });
    }
};  
