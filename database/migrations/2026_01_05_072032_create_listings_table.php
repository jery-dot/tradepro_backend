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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            $table->string('listing_code')->unique(); // e.g. "list_7891"

            $table->unsignedBigInteger('user_id');    // owner (seller)

            $table->string('title');
            $table->unsignedBigInteger('category_id');
            $table->string('category_name');
            $table->unsignedBigInteger('condition_id');
            $table->string('condition_name');

            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');

            $table->string('location_name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('description')->nullable();

            $table->enum('status', ['active', 'inactive', 'sold'])
                ->default('active');

            $table->timestamps();

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
        Schema::dropIfExists('listings');
    }
};
