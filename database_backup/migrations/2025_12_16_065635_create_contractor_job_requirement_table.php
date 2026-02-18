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
        Schema::create('contractor_job_requirement', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contractor_id')
                ->constrained('contractors')
                ->onDelete('cascade');

            $table->foreignId('job_requirement_id')
                ->constrained('job_requirements')
                ->onDelete('cascade');
                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_job_requirement');
    }
};
