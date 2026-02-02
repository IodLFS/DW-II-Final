<?php
require_once '../core/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->rowCount() > 0;
    }

    public function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->rowCount() > 0;
    }

    public function create($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $activationToken = bin2hex(random_bytes(32));

        $sql = "INSERT INTO users (username, email, password, name, activation_token, created_at) 
                VALUES (:username, :email, :password, :name, :token, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => $hashedPassword,
            'name'     => $data['name'],
            'token'    => $activationToken
        ]);
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false; 
    }
}
?>