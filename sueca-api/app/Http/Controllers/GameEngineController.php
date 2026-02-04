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
        
        
        // ... código anterior ...
        $players = DB::table('game_players')->where('game_id', $id)->orderBy('seat_index')->get();
        
        // ALTERADO: Mudámos de 4 para 1 apenas para testes. 
        // Quando o jogo estiver pronto, volta a colocar 4.
        if ($players->count() < 1) { 
            return response()->json(['error' => 'Precisas de jogadores suficientes'], 400);
        }
        // ... resto do código ...

        
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

    // [RF27] Jogar uma carta
    public function playCard(Request $request, $id)
    {
        $user = auth()->user();
        $cardPlayed = $request->input('card'); // Ex: '7h'

        // 1. Validar Jogo e Turno
        $game = DB::table('games')->where('id', $id)->first();
        if ($game->status !== 'started') {
            return response()->json(['error' => 'O jogo não está a decorrer'], 400);
        }
        if ($game->current_player_id != $user->id) {
            return response()->json(['error' => 'Não é a tua vez!'], 403);
        }

        // 2. Verificar se o jogador tem a carta
        $player = DB::table('game_players')->where('game_id', $id)->where('user_id', $user->id)->first();
        $hand = json_decode($player->cards_hand);
        
        if (!in_array($cardPlayed, $hand)) {
            return response()->json(['error' => 'Não tens essa carta!'], 400);
        }

        // 3. Remover carta da mão e atualizar BD
        $newHand = array_values(array_diff($hand, [$cardPlayed])); 
        DB::table('game_players')
            ->where('id', $player->id)
            ->update(['cards_hand' => json_encode($newHand)]);

        // 4. Adicionar à mesa
        $board = json_decode($game->board_state, true) ?? [];
        $board[] = [
            'card' => $cardPlayed,
            'player_id' => $user->id,
            'username' => $user->username // Guardamos o nome para mostrar na mesa
        ];

        // 5. Passar a vez (Próximo jogador sentado à esquerda)
        // Nota: Como estamos a testar com 1 jogador, a vez volta para ti (seat % 1).
        // Com 4 jogadores, seria ($seat + 1) % 4.
        $totalPlayers = DB::table('game_players')->where('game_id', $id)->count();
        $nextSeat = ($player->seat_index + 1) % $totalPlayers; 
        
        $nextPlayer = DB::table('game_players')
                        ->where('game_id', $id)
                        ->where('seat_index', $nextSeat)
                        ->first();

        // 6. Gravar estado
        DB::table('games')->where('id', $id)->update([
            'board_state' => json_encode($board),
            'current_player_id' => $nextPlayer->user_id
        ]);

        return response()->json(['message' => 'Jogada feita', 'board' => $board]);
    }
}