<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrabbleGame extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'board',
        'letters',
        'player1',
        'player2',
        'player1score',
        'player2score',
        'player1letters',
        'player2letters'
    ];

    protected $casts = [
        'board'          => 'array',
        'letters'        => 'array',
        'player1letters' => 'array',
        'player2letters' => 'array'
    ];
}
