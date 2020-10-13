<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Scrabble;


class ScrabbleController extends Controller
{
    protected $scrabbleService;

    public function __construct(Scrabble $scrabbleService) {
        $this->scrabbleService = $scrabbleService;
    }

    public function index($id)
    {
        return response()->json($this->scrabbleService->status($id));
    }

    public function prettyPrint($id)
    {
        return $this->scrabbleService->prettyPrint($id);
    }

    public function newGame(Request $request) {
        return response()
            ->json($this->scrabbleService->newGame($request->all()));
    }

    public function placeWord(Request $request, $id) {
        $this->scrabbleService->placeWord($id, $request->all());
    }
}