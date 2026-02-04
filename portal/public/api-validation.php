<?php
session_start();
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'models/User.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$value = trim($_GET['value'] ?? '');

if (empty($value)) {
    echo json_encode(['available' => false, 'message' => 'Campo vazio']);
    exit;
}

$userModel = new User();
$db = Database::connect();

switch ($action) {
    case 'email':
        $exists = $userModel->emailExists($value);
        echo json_encode([
            'available' => !$exists,
            'message' => $exists ? 'Email já registado' : 'Email disponível'
        ]);
        break;

    case 'username':
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $value]);
        $exists = $stmt->rowCount() > 0;
        
        echo json_encode([
            'available' => !$exists,
            'message' => $exists ? 'Nome de utilizador já existe' : 'Nome disponível'
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida']);
}
?>
