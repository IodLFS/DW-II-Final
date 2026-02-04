<?php
require_once '../core/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
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

    public function updateProfile($id, $name, $bio) {
        $sql = "UPDATE users SET name = :name, bio = :bio WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['name' => $name, 'bio' => $bio, 'id' => $id]);
    }


    public function updateAvatar($id, $avatarPath) {
        $sql = "UPDATE users SET avatar = :avatar WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['avatar' => $avatarPath, 'id' => $id]);
    }


    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }


    public function emailExists($email) {

        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->rowCount() > 0;
    }
}
?>