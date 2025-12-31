<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Hanafalah\LaravelSupport\Concerns\NowYouSeeMe;
use Hanafalah\ModuleDistribution\Models\Distribution\Distribution;

return new class extends Migration
{
    use NowYouSeeMe;

    private $__table;

    public function __construct()
    {
        $this->__table = app(config('database.models.Distribution', Distribution::class));
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $table_name = $this->__table->getTable();
        if (!$this->isTableExists()) {
            Schema::create($table_name, function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->string('order_no')->nullable();
                $table->string('distribution_no')->nullable();
                $table->string('flag')->nullable(false);
                $table->string('receiver_type', 50)->nullable(false);
                $table->string('receiver_id', 36)->nullable(false);
                $table->string('sender_type', 50)->nullable(false);
                $table->string('sender_id', 36)->nullable(false);
                $table->string('status', 50)->nullable(false);
                $table->string('author_receiver_type', 50)->nullable(true);
                $table->string('author_receiver_id', 36)->nullable(true);
                $table->string('author_sender_type', 50)->nullable(true);
                $table->string('author_sender_id', 36)->nullable(true);
                $table->timestamp('ordered_at')->nullable();
                $table->timestamp('distributed_at')->nullable();
                $table->json('props')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['receiver_type', 'receiver_id'], 'idx_receiver');
                $table->index(['sender_type', 'sender_id'], 'idx_sender');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
