<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('content');
            $table->json('sources')->nullable();
            $table->string('mode', 20)->nullable();
            $table->foreignId('ai_usage_log_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['ai_conversation_id', 'created_at']);
        });

        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->foreignId('ai_conversation_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->tinyInteger('rating')->nullable()->after('channel')->comment('1=helpful, -1=not helpful');
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ai_conversation_id');
            $table->dropColumn('rating');
        });

        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
