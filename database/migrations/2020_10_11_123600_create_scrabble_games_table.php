<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScrabbleGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scrabble_games', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->json('board');
            $table->json('letters');
            $table->string('player1');
            $table->string('player2');
            $table->smallInteger('player1score');
            $table->smallInteger('player2score');
            $table->json('player1letters');
            $table->json('player2letters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scrabble_games');
    }
}
