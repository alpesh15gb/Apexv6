<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_identifier');
            $table->string('device_name')->nullable();

            $table->timestamp('sync_started_at');
            $table->timestamp('sync_completed_at')->nullable();

            $table->integer('records_fetched')->default(0);
            $table->integer('records_synced')->default(0);
            $table->integer('records_failed')->default(0);
            $table->integer('records_skipped')->default(0);

            $table->string('status', 15)->default('running'); // running, success, partial, failed
            $table->text('error_log')->nullable();
            $table->json('sync_details')->nullable()->comment('Detailed sync info per record');

            $table->timestamps();

            $table->index(['device_identifier', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_sync_logs');
    }
};
