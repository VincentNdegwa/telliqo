<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('business_id')->constrained()->nullOnDelete();
            $table->foreignId('review_request_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['review_request_id']);
            $table->dropColumn(['customer_id', 'review_request_id']);
        });
    }
};
