<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            // Period represents the start of the billing period (e.g. first day of month)
            $table->date('period_start');
            $table->unsignedInteger('used')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'feature_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_usages');
    }
};
