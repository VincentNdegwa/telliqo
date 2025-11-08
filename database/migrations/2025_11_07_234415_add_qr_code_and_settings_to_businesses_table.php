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
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('qr_code_path')->nullable()->after('logo');
            $table->string('qr_code_url')->nullable()->after('qr_code_path');
            
            // Customization settings
            $table->text('custom_thank_you_message')->nullable()->after('qr_code_url');
            $table->string('brand_color_primary')->default('#3b82f6')->after('custom_thank_you_message');
            $table->string('brand_color_secondary')->default('#1e40af')->after('brand_color_primary');
            
            // Feature toggles
            $table->boolean('auto_approve_feedback')->default(true)->after('brand_color_secondary');
            $table->boolean('require_customer_name')->default(false)->after('auto_approve_feedback');
            $table->boolean('feedback_email_notifications')->default(true)->after('require_customer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'qr_code_path',
                'qr_code_url',
                'custom_thank_you_message',
                'brand_color_primary',
                'brand_color_secondary',
                'auto_approve_feedback',
                'require_customer_name',
                'feedback_email_notifications',
            ]);
        });
    }
};
