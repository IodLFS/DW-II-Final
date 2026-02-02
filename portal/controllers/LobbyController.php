<?php
require_once '../models/Game.php'; 

class LobbyController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }

        $gameModel = new Game();
        $rooms = $gameModel->getAllWaiting();

        $this->view('lobby', [
            'user_name' => $_SESSION['user_name'],
            'rooms' => $rooms
        ]);
    }
}
?>