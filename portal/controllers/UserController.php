<?php
require_once '../models/User.php';
$lang = $_SESSION['lang'] ?? 'pt';
$texts = include "../lang/$lang.php";

class UserController extends Controller {

    public function index() {
        header('Location: ' . BASE_URL . '/user/login');
        exit;
    }

    public function register() {
        $this->view('user/register');
    }

    public function store() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }

    $data = [
        'name'     => htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'username' => htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'email'    => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
        'password' => $_POST['password'] ?? ''
    ];

    if (empty($data['name']) || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        $_SESSION['error'] = "Todos os campos são obrigatórios.";
        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }

    if (strlen($data['password']) < 6) {
        $_SESSION['error'] = "A password deve ter pelo menos 6 caracteres.";
        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email inválido.";
        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }

    $userModel = new User();

    if ($userModel->emailExists($data['email'])) {
        $_SESSION['error'] = "Este email já está registado.";
        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }

    if ($userModel->usernameExists($data['username'])) {
        $_SESSION['error'] = "Este nome de utilizador já existe.";
        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }

    $data['password'] = password_hash($data['password'], PASSWORD_ARGON2ID);

    if ($userModel->create($data)) {
        $_SESSION['success'] = "Registo bem-sucedido! Verifique o seu email para ativar a conta.";
        header('Location: ' . BASE_URL . '/user/login');
        exit;
    } else {
        $_SESSION['error'] = "Erro ao registar a conta. Tente novamente.";
        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }
}

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('user/login');
            return;
        }

        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Email e password são obrigatórios.";
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }

        $userModel = new User();
        $user = $userModel->login($email, $password);

        if ($user) {
            $ch = curl_init(API_BASE_URL . '/api/login');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(['email' => $email, 'password' => $password]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $_SESSION['error'] = "Erro de conexão com o motor de jogo: " . htmlspecialchars($curlError);
                header('Location: ' . BASE_URL . '/user/login');
                exit;
            }

            $apiData = json_decode($response, true);

            if ($httpCode === 200 && isset($apiData['access_token'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['jwt_token'] = $apiData['access_token'];
                $_SESSION['authenticated'] = true;
                
                header('Location: ' . BASE_URL . '/lobby');
                exit;
            } else {
                $_SESSION['error'] = "Erro ao autenticar com o motor de jogo.";
                header('Location: ' . BASE_URL . '/user/login');
                exit;
            }
        } else {
            $_SESSION['error'] = "Email ou password incorretos.";
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }
    }

    public function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: ' . BASE_URL . '/user/login');
    exit;
}

    // [RF10, RF11] Página de Editar Perfil
    public function profile() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/user/login');
        exit;
    }

    $userModel = new User();
    $userId = $_SESSION['user_id'];
    $message = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        $userModel->updateProfile($userId, $name, $bio);
        $message = "Perfil atualizado com sucesso!";
        $_SESSION['user_name'] = $name;

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($_FILES['avatar']['tmp_name']);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($mimeType, $allowedMimes)) {
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $newName = "user_" . $userId . "_" . time() . "." . $ext;
                $uploadDir = "../public/uploads/";
                $dest = $uploadDir . $newName;

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                    $userModel->updateAvatar($userId, $newName);
                    $message .= " Avatar atualizado.";
                } else {
                    $message .= " Erro ao mover o ficheiro para a pasta de destino.";
                }

            } else {
                $message .= " Erro: O ficheiro enviado não é uma imagem válida (JPG, PNG ou GIF).";
            }
        }
    }

    $user = $userModel->findById($userId);
    $this->view('user/profile', ['user' => $user, 'message' => $message]);
}

    public function checkEmail() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = json_decode(file_get_contents("php://input"));
            $email = $data->email ?? '';

            $userModel = new User();
            $exists = $userModel->emailExists($email);

            header('Content-Type: application/json');
            echo json_encode(['exists' => $exists]);
            exit;
        }

        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            echo "Se o email existir, receberá um link de recuperação.";
        } else {
            $this->view('user/forgot_password');
        }
    }
}
?>