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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->integer('total_requests_sent')->default(0);
            $table->integer('total_feedbacks')->default(0);
            $table->timestamp('last_request_sent_at')->nullable();
            $table->timestamp('last_feedback_at')->nullable();
            $table->boolean('opted_out')->default(false);
            $table->timestamps();
            
            $table->unique(['business_id', 'email']);
            $table->index(['business_id', 'opted_out']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
