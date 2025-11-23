<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('paypal_plan_id_monthly', 50)->nullable()->after('price_usd_yearly');
            $table->string('paypal_plan_id_yearly', 50)->nullable()->after('paypal_plan_id_monthly');
            $table->string('paddle_plan_id_monthly', 50)->nullable()->after('paypal_plan_id_yearly');
            $table->string('paddle_plan_id_yearly', 50)->nullable()->after('paddle_plan_id_monthly');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'paypal_plan_id_monthly',
                'paypal_plan_id_yearly',
                'paddle_plan_id_monthly',
                'paddle_plan_id_yearly',
            ]);
        });
    }
};
