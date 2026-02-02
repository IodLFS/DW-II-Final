<?php
require_once '../models/Game.php';

class GameController extends Controller {

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $roomName = isset($_POST['room_name']) ? trim($_POST['room_name']) : '';

            if (empty($roomName)) {
                die("O nome da sala é obrigatório.");
            }

            $gameModel = new Game();
            $creatorId = $_SESSION['user_id'];

            $gameId = $gameModel->create($roomName, $creatorId);

            if ($gameId) {
                $gameModel->addPlayer($gameId, $creatorId, 0);

                header('Location: ' . BASE_URL . '/lobby');
                exit;
            } else {
                die("Erro ao criar a sala.");
            }
        }
    }

    public function join($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }

        $gameModel = new Game();
        $userId = $_SESSION['user_id'];

        if ($gameModel->isPlayerInGame($id, $userId)) {
            header('Location: ' . BASE_URL . '/game/wait/' . $id);
            exit;
        }

        $nextSeat = $gameModel->getNextSeat($id);
        
        if ($nextSeat !== false) {
            $gameModel->addPlayer($id, $userId, $nextSeat);
            header('Location: ' . BASE_URL . '/game/wait/' . $id);
            exit;
        } else {
            die("A sala está cheia!");
        }
    }

    public function wait($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }

        $gameModel = new Game();
        $game = $gameModel->getGame($id);
        $players = $gameModel->getPlayers($id);

        if (!$game) {
            die("Sala não encontrada.");
        }

        $this->view('game/waiting', [
            'game' => $game,
            'players' => $players,
            'user_id' => $_SESSION['user_id']
        ]);
    }

    public function play($id) {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['jwt_token'])) {
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }
        
        $this->view('game/board', ['game_id' => $id]);
    }
}
?>