<?php

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Comment::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('reaction', 32);
            $table->timestamps();

            $table->unique(['comment_id', 'user_id', 'reaction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
