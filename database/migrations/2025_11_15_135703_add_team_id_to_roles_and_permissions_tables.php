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
        // Add team_id to roles table only (permissions remain global)
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->after('id');
            $table->foreign('team_id')->references('id')->on('businesses')
                ->onUpdate('cascade')->onDelete('cascade');
            
            // Update unique constraint to allow same role name per business
            $table->dropUnique(['name']);
            $table->unique(['name', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropUnique(['name', 'team_id']);
            $table->unique(['name']);
            $table->dropColumn('team_id');
        });
    }
};
