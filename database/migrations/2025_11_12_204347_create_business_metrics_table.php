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
        Schema::create('business_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->date('metric_date');
            $table->integer('total_feedback')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->integer('promoters')->default(0);
            $table->integer('passives')->default(0);
            $table->integer('detractors')->default(0);
            $table->integer('nps')->default(0);
            $table->integer('positive_count')->default(0);
            $table->integer('neutral_count')->default(0);
            $table->integer('negative_count')->default(0);
            $table->integer('not_determined_count')->default(0);
            $table->json('top_keywords')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'metric_date']);
            $table->index(['business_id', 'metric_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_metrics');
    }
};
