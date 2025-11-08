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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->nullable();
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_public')->default(false);
            $table->timestamp('replied_at')->nullable();
            $table->text('reply_text')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'moderation_status']);
            $table->index(['business_id', 'is_public']);
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
