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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('waiting');
            $table->integer('score_team_a')->default(0);
            $table->integer('score_team_b')->default(0);
            $table->integer('current_round')->default(1);
            $table->foreignId('current_player')->nullable()->constrained('users')->onDelete('set null');
            $table->json('players')->nullable();
            $table->json('hands')->nullable();
            $table->json('table')->nullable();
            $table->json('rounds')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
