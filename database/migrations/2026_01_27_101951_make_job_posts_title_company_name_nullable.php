<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Make job_posts.title and company_name nullable
     */
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('company_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->string('company_name')->nullable(false)->change();
        });
    }

};
