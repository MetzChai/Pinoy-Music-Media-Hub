<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per-user favorites / playlist membership replace the old global
     * songs.is_favorite + songs.in_playlist flags (which had no owner).
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'song_id']);
        });

        Schema::create('playlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'song_id']);
        });

        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn(['is_favorite', 'in_playlist']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->boolean('is_favorite')->default(false);
            $table->boolean('in_playlist')->default(false);
        });

        Schema::dropIfExists('playlist_items');
        Schema::dropIfExists('favorites');
    }
};
