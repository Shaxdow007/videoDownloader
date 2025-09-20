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
        Schema::create('download_history', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048);
            $table->string('title')->nullable();
            $table->string('filename');
            $table->string('format', 10);
            $table->string('quality', 20)->nullable();
            $table->bigInteger('filesize')->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('source', 50)->default('unknown'); // external_api, yt_dlp, direct_parsing
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->json('metadata')->nullable(); // Store additional info like thumbnail, duration, etc.
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['ip_address', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('platform');
            $table->index('downloaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_history');
    }
};