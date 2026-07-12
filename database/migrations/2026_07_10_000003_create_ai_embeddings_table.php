<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_embeddings', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id');
            $table->unsignedSmallInteger('chunk_index')->default(0);
            $table->string('title', 255);
            $table->text('content');
            $table->json('embedding');
            $table->timestamps();

            $table->unique(['source_type', 'source_id', 'chunk_index']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_embeddings');
    }
};
