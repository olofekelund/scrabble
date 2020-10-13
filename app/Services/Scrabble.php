<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\ScrabbleGame;

class Scrabble
{
    protected $letterPoints = [
        "a" => 1,
        "b" => 3,
        "c" => 3,
        "d" => 2,
        "e" => 1,
        "f" => 4,
        "g" => 2,
        "h" => 4,
        "i" => 1,
        "j" => 8,
        "k" => 5,
        "l" => 1,
        "m" => 3,
        "n" => 1,
        "o" => 1,
        "p" => 3,
        "q" => 10,
        "r" => 1,
        "s" => 1,
        "t" => 1,
        "u" => 1,
        "v" => 4,
        "w" => 4,
        "x" => 8,
        "y" => 4,
        "z" => 10
    ];

    public function status($id)
    {
        $game = ScrabbleGame::find($id);

        if (!$game) {
            abort(404);
        }

        return [
            'player1score'   => $game->player1score,
            'player2score'   => $game->player2score,
            'remainingTiles' => count($game->letters),
            'player1letters' => $game->player1letters,
            'player2letters' => $game->player2letters
        ];
    }

    public function prettyPrint($id)
    {
        $game = ScrabbleGame::find($id);

        if (!$game) {
            abort(404);
        }

        $output = "";

        foreach ($game->board as $key => $row) {
            $output .= "-------------------------------\n";
            $output .= "|";

            for ($i=0; $i < 15; $i++) {
                $output .= $row[$i]['letter'] . "|";
            }
            $output .= "\n";
        }

        $output .= "-------------------------------\n";
        $output .= "\n\n Player1 score: " . $game->player1score . "  Name: " . $game->player1 . " Letters: ";
        foreach ($game->player1letters as $letter) {
            $output .= " " . $letter;
        }

        $output .= "\n\n\n-------------------------------\n";
        $output .= "\n\n Player2 score: " . $game->player2score . "  Name: " . $game->player2 . " Letters: ";
        foreach ($game->player2letters as $letter) {
            $output .= " " . $letter;
        }

        $output .= "\n\n\n-------------------------------\n";
        $output .= "\n\n Remaining letters: " . count($game->letters);

        return $output;
    }

    public function newGame($data) {
        $board = array_fill(0, 15, array_fill(
                0, 15, ['player' => 0, 'letter' => ' ', 'points' => 0]
            )
        );
        $json = json_decode(Storage::disk('local')->get('public/letters.json'));
        $letters = [];
        $player1letters = [];
        $player2letters = [];

        foreach ($json->letters as $letter => $letterData) {
            $letterPoints[$letter] = $letterData->points;

            for ($i = 0; $i < $letterData->tiles; $i++) {
                $letters[] = $letter;
            }
        }

        shuffle($letters);

        for ($i = 0; $i < 7; $i++) {
            $player1letters[] = array_pop($letters);
            $player2letters[] = array_pop($letters);
        }

        $scrabbleBoard = ScrabbleGame::create([
            'letters'        => $letters,
            'board'          => $board,
            'player1'        => $data['player1'],
            'player2'        => $data['player2'],
            'player1score'   => 0,
            'player2score'   => 0,
            'player1letters' => $player1letters,
            'player2letters' => $player2letters
        ]);

        return $scrabbleBoard;
    }

    public function placeWord($id, $data) {
        $game                      = ScrabbleGame::find($id);
        $data['coordinates']['x'] -= 1;
        $data['coordinates']['y'] -= 1;
        $wordArray                 = str_split($data['word']);
        $score                     = 0;
        $board                     = $game->board;
        $letters                   = $game->letters;
        $isFirstMove               = $game->player1score === 0 && $game->player2score === 0;

        if (!$game) {
            abort(404);
        }
        if (!$isFirstMove && !$this->verifyNeighbors($wordArray, $data['coordinates'], $data['direction'], $game)) {
            abort(400);
        }
        if(!$this->verifyWord($wordArray, $game, $data['player'])) {
            abort(400);
        }
        if(!$this->verifyCoordinates($wordArray, $data['coordinates'], $data['direction'], $game)) {
            abort(400);
        }

        foreach ($wordArray as $i => $letter) {
            $x = $data['coordinates']['x'];
            $y = $data['coordinates']['y'];

            if ($data['direction'] === 'horizontal') {
                $x += $i;
            } else {
                $y += $i;
            }

            $board[$y][$x] = [
                'letter' => $letter,
                'player' => $data['player'],
                'points' => $this->letterPoints[$data['word'][$i]]
            ];

            $score += $this->letterPoints[$data['word'][$i]];
        }

        if ($data['player'] === 1) {
            $game->player1score = $game->player1score + $score;
            $playerLetters = array_diff($game->player1letters, $wordArray);

            while (count($playerLetters) < 7) {
                $playerLetters[] = array_pop($letters);
            }
            $game->player1letters = $playerLetters;
        } else if($data['player'] === 2) {
            $game->player2score = $game->player2score + $score;
            $playerLetters = array_diff($game->player2letters, $wordArray);

            while (count($playerLetters) < 7) {
                $playerLetters[] = array_pop($letters);
            }
            $game->player2letters = $playerLetters;
        }

        $game->board = $board;
        $game->letters = $letters;
        $game->save();
    }

    private function verifyNeighbors($word, $coordinates, $direction, $game) {
        $neighbors = [
            [-1, 0],
            [0, -1],
            [0, 1],
            [1, 0]
        ];

        foreach ($word as $i => $letter) {
            $x = $coordinates['x'];
            $y = $coordinates['y'];

            if ($direction === 'horizontal') {
                $x += $i;
            } else {
                $y += $i;
            }

            foreach ($neighbors as $offset) {
                $x += $offset[0];
                $y += $offset[1];

                if (!($x < 0 || $y < 0 || $x > 14 || $y > 14)) {
                    if ($game->board[$y][$x]['letter'] !== ' ') return true;
                }
            }
        }

        return false;
    }

    private function verifyCoordinates($word, $coordinates, $direction, $game) {
        if (($direction === 'horizontal' && $coordinates['x'] + count($word) > 15) ||
            ($direction === 'vertical' && $coordinates['y'] + count($word) > 15) ||
            ($coordinates['x'] < 0) ||
            ($coordinates['y'] < 0)
        ) {
            return false;
        }

        foreach ($word as $i => $letter) {
            $x = $coordinates['x'];
            $y = $coordinates['y'];

            if ($direction === 'horizontal') {
                $x += $i;
            } else {
                $y += $i;
            }

            if ($game->board[$y][$x]['letter'] !== ' ') {
                return false;
            }
        }

        return true;
    }

    private function verifyWord($word, $game, $player) {
        $remainingLetters = $player === 1 ? $game->player1letters : $game->player2letters;

        foreach ($word as $letter) {
            $index = array_search($letter, $remainingLetters);

            if ($index === false) return false;

            array_splice($remainingLetters, $index, 1);
        }
        return true;
    }
}
