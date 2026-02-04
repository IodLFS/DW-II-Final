<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class GameEngineController extends Controller
{


public function startGame($id)
{
    $user = auth('sanctum')->user();
    $game = DB::table('games')->where('id', $id)->first();
    

    if (!$game || $game->creator_id != $user->id) {
        return response()->json(['error' => 'Apenas o dono pode iniciar'], 403);
    }
    
    $players = DB::table('game_players')->where('game_id', $id)->orderBy('seat_index')->get();

    if ($players->count() !== 4) {
        return response()->json(['error' => 'A sala precisa de exatamente 4 jogadores para iniciar!'], 400);
    }


    $suits = ['h', 's', 'd', 'c']; 
    $ranks = ['2', '3', '4', '5', '6', 'Q', 'J', 'K', '7', 'A'];
    $deck = [];
    foreach ($suits as $suit) {
        foreach ($ranks as $rank) { $deck[] = $rank . $suit; }
    }
    
    shuffle($deck); 


    $trumpCard = $deck[39]; 
    $trumpSuit = substr($trumpCard, -1); 

    $hands = array_chunk($deck, 10);
    
    foreach ($players as $index => $player) {
        DB::table('game_players')
            ->where('id', $player->id)
            ->update(['cards_hand' => json_encode($hands[$index])]);
    }


    $randomStartPlayer = $players->random(); 

    DB::table('games')->where('id', $id)->update([
        'status' => 'started',
        'trump_card' => $trumpCard,
        'trump_suit' => $trumpSuit,
        'current_player_id' => $randomStartPlayer->user_id,
        'board_state' => json_encode([]),
        'score_team_a' => 0,
        'score_team_b' => 0
    ]);

    return response()->json(['message' => 'Jogo iniciado!']);
}


    public function getGameState($id)
    {
        $user = auth('sanctum')->user();
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
            'my_hand' => $myPlayer ? json_decode($myPlayer->cards_hand) : [],
            'my_seat' => $myPlayer ? $myPlayer->seat_index : -1,
            'trump' => [
                'card' => $game->trump_card,
                'suit' => $game->trump_suit
            ],
            'table_cards' => json_decode($game->board_state),
            'current_turn' => $game->current_player_id,
            'scores' => [
                'team_A' => $game->score_team_a,
                'team_B' => $game->score_team_b
            ],
            'winner' => $game->winner_team,
            'players' => $opponents
        ]);
    }


    public function playCard(Request $request, $id)
    {
        $user = auth('sanctum')->user();
        $cardPlayed = $request->input('card');


        $game = DB::table('games')->where('id', $id)->first();
        if ($game->status !== 'started') {
            return response()->json(['error' => 'Jogo não decorre'], 400);
        }
        if ($game->current_player_id != $user->id) {
            return response()->json(['error' => 'Não é a tua vez!'], 403);
        }

        $player = DB::table('game_players')->where('game_id', $id)->where('user_id', $user->id)->first();
        $hand = json_decode($player->cards_hand);

        if (!in_array($cardPlayed, $hand)) {
            return response()->json(['error' => 'Não tens essa carta!'], 400);
        }


        $board = json_decode($game->board_state, true) ?? [];
        
        if (count($board) > 0) {
            $firstCard = $board[0]['card'];
            $suitLed = substr($firstCard, -1);
            $suitPlayed = substr($cardPlayed, -1);


            if ($suitPlayed !== $suitLed) {

                $hasSuit = false;
                foreach ($hand as $c) {
                    if (substr($c, -1) === $suitLed) {
                        $hasSuit = true;
                        break;
                    }
                }
                if ($hasSuit) {
                    return response()->json(['error' => 'Tens de assistir ao naipe! (' . $suitLed . ')'], 400);
                }
            }
        }


        $newHand = array_values(array_diff($hand, [$cardPlayed]));
        DB::table('game_players')->where('id', $player->id)->update(['cards_hand' => json_encode($newHand)]);


        $board[] = [
            'card' => $cardPlayed,
            'player_id' => $user->id,
            'username' => $user->username,
            'seat_index' => $player->seat_index
        ];



        $totalPlayers = DB::table('game_players')->where('game_id', $id)->count();
        $cardsPerTrick = ($totalPlayers < 4) ? $totalPlayers : 4; 
        $cardsPerTrick = 4;
        $trickWinnerId = null;
        $points = 0;
        $gameFinished = false;

        if (count($board) >= $cardsPerTrick) {

            $winnerIndex = $this->calculateTrickWinner($board, $game->trump_suit);
            $winningPlay = $board[$winnerIndex];
            $trickWinnerId = $winningPlay['player_id'];

            foreach ($board as $play) {
                $points += $this->getCardPoints($play['card']);
            }


            if ($winningPlay['seat_index'] % 2 == 0) {
                DB::table('games')->where('id', $id)->increment('score_team_a', $points);
            } else {
                DB::table('games')->where('id', $id)->increment('score_team_b', $points);
            }


            if (empty($newHand)) {
                $gameFinished = true;
                $finalGame = DB::table('games')->where('id', $id)->first();
                $winnerTeam = ($finalGame->score_team_a > $finalGame->score_team_b) ? 'Equipa A' : 'Equipa B';
                if ($finalGame->score_team_a == $finalGame->score_team_b) $winnerTeam = 'Empate';

                DB::table('games')->where('id', $id)->update([
                    'status' => 'finished',
                    'winner_team' => $winnerTeam
                ]);
            }


            $board = [];
            

            $nextPlayerId = $trickWinnerId;

        } else {

            $nextSeat = ($player->seat_index + 1) % $totalPlayers;

            $nextPlayerObj = DB::table('game_players')
                ->where('game_id', $id)
                ->where('seat_index', $nextSeat)
                ->first();
            $nextPlayerId = $nextPlayerObj->user_id;
        }


        if (!$gameFinished) {
            DB::table('games')->where('id', $id)->update([
                'board_state' => json_encode($board),
                'current_player_id' => $nextPlayerId
            ]);
        }

        return response()->json([
            'message' => 'Jogada feita',
            'trick_finished' => ($trickWinnerId !== null),
            'game_finished' => $gameFinished,
            'points' => $points
        ]);
    }



    private function calculateTrickWinner($board, $trumpSuit) {
        $leadSuit = substr($board[0]['card'], -1);
        $bestIndex = 0;
        $bestRankValue = -1;
        $highestTrumpValue = -1;
        $trumpPlayed = false;

        foreach ($board as $index => $play) {
            $card = $play['card'];
            $suit = substr($card, -1);
            $rank = substr($card, 0, -1);
            $val = $this->getRankValue($rank);

            if ($suit === $trumpSuit) {
                if ($val > $highestTrumpValue) {
                    $highestTrumpValue = $val;
                    $bestIndex = $index;
                    $trumpPlayed = true;
                }
            } 
            else if (!$trumpPlayed && $suit === $leadSuit) {
                if ($val > $bestRankValue) {
                    $bestRankValue = $val;
                    $bestIndex = $index;
                }
            }
        }
        return $bestIndex;
    }

    private function getCardPoints($card) {
        $rank = substr($card, 0, -1);
        $points = ['A'=>11, '7'=>10, 'K'=>4, 'J'=>3, 'Q'=>2];
        return $points[$rank] ?? 0;
    }

    private function getRankValue($rank) {

        $order = ['A'=>11, '7'=>10, 'K'=>9, 'J'=>8, 'Q'=>7, '6'=>6, '5'=>5, '4'=>4, '3'=>3, '2'=>2];
        return $order[$rank] ?? 0;
    }
}