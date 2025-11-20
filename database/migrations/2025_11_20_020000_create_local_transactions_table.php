<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('local_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('local_subscription_id')->nullable()->constrained('local_subscriptions')->nullOnDelete();
            $table->string('provider', 50);
            $table->string('status', 50)->index();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('external_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamps();

            $table->index(['business_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_transactions');
    }
};
