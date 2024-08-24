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
        Schema::create('user_youtubes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id');
            $table->foreign('channel_id')->references('id')->on('user_channels')->onDelete('cascade');
            $table->string('guild_id');
            $table->string('youtube_id');
            $table->string('name');
            $table->string('profile');
            $table->string('last');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_youtubes');
    }
};
