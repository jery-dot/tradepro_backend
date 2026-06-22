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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // 'Laborer', 'Contractor', 'Apprentice'
            $table->string('stripe_price_id')->unique(); // 'price_laborer_monthly'
            $table->decimal('price', 10, 2);  // 19.00, 59.00, 9.99
            $table->string('currency', 3)->default('USD');
            $table->string('billing_cycle')->default('monthly'); // 'monthly', 'yearly'
            $table->integer('trial_days')->default(0); // 90 for Apprentice
            $table->json('features');         // ["unlimited_applications", "direct_messaging"]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
