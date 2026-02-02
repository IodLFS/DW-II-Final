<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use App\Http\Controllers\Controller;

class GameEngineController extends Controller
{
    
    public function startGame($id)
    {
        $user = auth()->user();
        
        
        $game = DB::table('games')->where('id', $id)->first();

        
        if (!$game || $game->creator_id != $user->id) {
            return response()->json(['error' => 'Apenas o dono pode iniciar'], 403);
        }
        
        
        $players = DB::table('game_players')->where('game_id', $id)->orderBy('seat_index')->get();
        if ($players->count() < 4) {
            return response()->json(['error' => 'Precisas de 4 jogadores'], 400);
        }

        
        $suits = ['h', 's', 'd', 'c']; 
        $ranks = ['2', '3', '4', '5', '6', 'Q', 'J', 'K', '7', 'A'];
        $deck = [];
        
        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $deck[] = $rank . $suit;
            }
        }
        
        shuffle($deck); 

        
        $hands = array_chunk($deck, 10);
        
        
        $trumpCard = $hands[0][0]; 
        $trumpSuit = substr($trumpCard, -1);

        
        foreach ($players as $index => $player) {
            DB::table('game_players')
                ->where('id', $player->id)
                ->update(['cards_hand' => json_encode($hands[$index])]);
        }

        DB::table('games')->where('id', $id)->update([
            'status' => 'started',
            'trump_card' => $trumpCard,
            'trump_suit' => $trumpSuit,
            'current_player_id' => $players[0]->user_id, 
            'board_state' => json_encode([])
        ]);

        return response()->json(['message' => 'Jogo iniciado!']);
    }

    public function getGameState($id)
    {
        $user = auth()->user();
        $game = DB::table('games')->where('id', $id)->first();
        
        $myPlayer = DB::table('game_players')
            ->where('game_id', $id)
            ->where('user_id', $user->id)
            ->first();

        $opponents = DB::table('game_players')
            ->join('users', 'game_players.user_id', '=', 'users.id')
            ->where('game_id', $id)
            ->select('users.username', 'game_players.seat_index', 'users.avatar')
            ->get();

        return response()->json([
            'status' => $game->status,
            'my_hand' => json_decode($myPlayer->cards_hand),
            'my_seat' => $myPlayer->seat_index,
            'trump' => [
                'card' => $game->trump_card,
                'suit' => $game->trump_suit
            ],
            'table_cards' => json_decode($game->board_state),
            'current_turn' => $game->current_player_id,
            'players' => $opponents
        ]);
    }
}