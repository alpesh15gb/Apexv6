<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    Schema::dropIfExists('regularization_requests');

    Schema::create('regularization_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->date('date');
        $table->time('punch_in_time')->nullable();
        $table->time('punch_out_time')->nullable();
        $table->text('reason'); // Why they need regularization
        $table->string('status')->default('pending'); // pending, approved, rejected
        $table->foreignId('approver_id')->nullable()->constrained('users');
        $table->text('remarks')->nullable(); // Approver remarks
        $table->timestamps();

        // Indexes
        $table->index(['user_id', 'date']);
        $table->index('status');
    });
    echo "Migration successful via script.\n";
} catch (\Exception $e) {
    echo "Migration Failed:\n" . $e->getMessage() . "\n";
}
