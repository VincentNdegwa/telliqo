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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // User who performed the action (null for system actions)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_type')->default('user'); // 'user' or 'system'
            $table->string('user_email')->nullable(); // Store email for reference
            
            // Auditable model information
            $table->string('auditable_type'); // Model class name
            $table->unsignedBigInteger('auditable_id'); // Model ID
            $table->index(['auditable_type', 'auditable_id']); // Composite index for queries
            
            // Action details
            $table->string('event'); // created, updated, deleted, restored, etc.
            $table->string('ip_address', 45)->nullable(); // IPv4 or IPv6
            $table->string('user_agent')->nullable();
            
            // Encrypted data columns
            $table->text('old_values')->nullable(); // Encrypted JSON of old values
            $table->text('new_values')->nullable(); // Encrypted JSON of new values
            $table->text('changes')->nullable(); // Encrypted JSON of what changed
            
            // Metadata
            $table->string('url')->nullable(); // Request URL
            $table->string('method')->nullable(); // HTTP method (GET, POST, etc.)
            $table->json('additional_data')->nullable(); // Any extra context
            $table->text('tags')->nullable(); // Searchable tags (comma-separated)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
