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
        Schema::table('review_requests', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('sent_at');
            $table->boolean('is_scheduled')->default(false)->after('status');
            $table->string('send_mode')->default('now')->after('is_scheduled'); // 'now', 'scheduled', 'manual'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_requests', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'is_scheduled', 'send_mode']);
        });
    }
};
