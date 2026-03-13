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
        Schema::create('job_acceptances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('labor_id')->constrained('users')->onDelete('cascade');

            // Who accepted the job
            $table->unsignedBigInteger('acceptor_id');
            $table->string('acceptor_type');
            // "contractor" OR "subcontractor"

            $table->enum('status', ['accepted', 'completed', 'cancelled'])
                ->default('accepted');

            $table->timestamps();

            // $table->unique(['job_post_id']); // only one accept per job
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_acceptances');
    }
};
