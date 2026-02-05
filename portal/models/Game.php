<?php
require_once '../core/Database.php';

class Game {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function create($name, $creatorId) {
        $sql = "INSERT INTO games (code, owner_id, status, created_at) VALUES (:code, :owner_id, 'waiting', NOW())";
        $stmt = $this->db->prepare($sql);
        $code = substr(md5(uniqid()), 0, 6);
        if ($stmt->execute(['code' => $code, 'owner_id' => $creatorId])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getAllWaiting() {
        $sql = "SELECT g.*, u.username as creator_name, (SELECT COUNT(*) FROM game_players gp WHERE gp.game_id = g.id) as player_count FROM games g JOIN users u ON g.owner_id = u.id WHERE g.status = 'waiting' ORDER BY g.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function addPlayer($gameId, $userId, $seatIndex) {
        $sql = "INSERT INTO game_players (game_id, user_id, seat_index, created_at) VALUES (:game_id, :user_id, :seat_index, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['game_id' => $gameId, 'user_id' => $userId, 'seat_index' => $seatIndex]);
    }

    public function getGame($id) {
        $stmt = $this->db->prepare("SELECT * FROM games WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getPlayers($gameId) {
        $sql = "SELECT u.username, u.avatar, gp.seat_index 
                FROM game_players gp
                JOIN users u ON gp.user_id = u.id
                WHERE gp.game_id = :game_id
                ORDER BY gp.seat_index ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['game_id' => $gameId]);
        return $stmt->fetchAll();
    }

    public function isPlayerInGame($gameId, $userId) {
        $sql = "SELECT id FROM game_players WHERE game_id = :game_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['game_id' => $gameId, 'user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
    
    public function getNextSeat($gameId) {
        $sql = "SELECT seat_index FROM game_players WHERE game_id = :game_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['game_id' => $gameId]);
        $takenSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);

        for ($i = 0; $i < 4; $i++) {
            if (!in_array($i, $takenSeats)) {
                return $i;
            }
        }
        return false;
    }


    public function getHistory($userId) {
        $sql = "SELECT g.*, 
                (SELECT COUNT(*) FROM game_players WHERE game_id = g.id) as total_players
                FROM games g
                JOIN game_players gp ON g.id = gp.game_id
                WHERE gp.user_id = :user_id 
                AND g.status = 'finished'
                ORDER BY g.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
?>