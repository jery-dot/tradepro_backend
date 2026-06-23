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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->unique();
            $table->string('stripe_status')->nullable();
            $table->string('default_payment_method')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->foreignId('active_subscription_id')->nullable()->constrained('user_subscriptions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // drop foreign key first
            $table->dropForeign(['active_subscription_id']);

            // drop columns
            $table->dropColumn([
                'stripe_id',
                'stripe_status',
                'default_payment_method',
                'trial_ends_at',
                'active_subscription_id'
            ]);
        });
    }
};
