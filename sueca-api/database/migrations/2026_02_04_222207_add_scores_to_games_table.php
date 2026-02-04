<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
    Schema::table('games', function (Blueprint $table) {
        $table->integer('score_team_a')->default(0); // Equipa Par (0, 2)
        $table->integer('score_team_b')->default(0); // Equipa Ímpar (1, 3)
        $table->string('winner_team')->nullable();   // 'Team A' ou 'Team B'
    });
    }

    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['score_team_a', 'score_team_b', 'winner_team']);
        });
    }
};
