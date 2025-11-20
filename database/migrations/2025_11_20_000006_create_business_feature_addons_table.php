<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_feature_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('feature_addon_id')->constrained('feature_addons')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            // Optional: tie addon to a specific billing period
            $table->date('period_start')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_feature_addons');
    }
};
