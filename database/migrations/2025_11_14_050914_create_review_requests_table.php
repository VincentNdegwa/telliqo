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
        Schema::create('review_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('unique_token', 64)->unique();
            $table->enum('status', ['pending', 'opened', 'completed', 'expired'])->default('pending');
            $table->string('subject');
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('reminder_sent_count')->default(0);
            $table->timestamp('last_reminder_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['business_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('unique_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_requests');
    }
};
