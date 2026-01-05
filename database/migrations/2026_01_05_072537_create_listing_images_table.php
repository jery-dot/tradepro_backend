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
        Schema::create('listing_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('listing_id');
            $table->string('image_code')->unique(); // e.g. "img_101"
            $table->string('path');                 // stored file path

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('listing_id')
                ->references('id')
                ->on('listings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_images');
    }
};
