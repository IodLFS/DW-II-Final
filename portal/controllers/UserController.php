<?php
require_once '../models/User.php';
$lang = $_SESSION['lang'] ?? 'pt';
$texts = include "../lang/$lang.php";

class UserController extends Controller {

    public function register() {
        $this->view('user/register');
    }

    public function store() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // 1. Recolha e limpeza de dados
        $data = [
            'name'     => trim($_POST['name']),
            'username' => trim($_POST['username']),
            'email'    => trim($_POST['email']),
            'password' => $_POST['password'] // Ainda em texto limpo para validação
        ];
        if (empty($data['name']) || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $_SESSION['error'] = $this->lang['error_fill_all']; // Usa tradução 
            header('Location: ' . BASE_URL . '/user/register');
            exit;
        }

        // 2. Validação de campos vazios
        if (empty($data['name']) || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $_SESSION['error'] = "Por favor preencha todos os campos.";
            header('Location: ' . BASE_URL . '/user/register');
            exit;
        }

        $userModel = new User();

        // 3. Verificação de duplicados (RF04)
        if ($userModel->emailExists($data['email'])) {
            $_SESSION['error'] = "Erro: Este email já está registado.";
            header('Location: ' . BASE_URL . '/user/register');
            exit;
        }
        if ($userModel->usernameExists($data['username'])) {
            $_SESSION['error'] = "Erro: Este nome de utilizador já existe.";
            header('Location: ' . BASE_URL . '/user/register');
            exit;
        }
        
        if ($userModel->emailExists($data['email'])) {
            $_SESSION['error'] = $this->lang['email_exists']; // Usa tradução 
            header('Location: ' . BASE_URL . '/user/register');
            exit;
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        if ($userModel->create($data)) {
            $_SESSION['success'] = $this->lang['register_success']; // Usa tradução 
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }
    } else {
        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }
}

    public function login() {
        $this->view('user/login');
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $userModel = new User();
            $user = $userModel->login($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_username'] = $user['username'];
                
                $data = json_encode(['email' => $email, 'password' => $password]);

                $ch = curl_init(API_BASE_URL . '/login');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $apiData = json_decode($response, true);

                // Após o curl_exec($ch) 
                if ($httpCode == 200 && isset($apiData['access_token'])) {
                    $_SESSION['jwt_token'] = $apiData['access_token'];
                    header('Location: ' . BASE_URL . '/lobby');
                    exit;
                } else {
                    // Se a API falhar, impede a entrada no lobby
                    $_SESSION['error'] = "Erro de autenticação no motor de jogo. Tente mais tarde.";
                    header('Location: ' . BASE_URL . '/user/login');
                    exit;
                }

            } else {
                echo "Email ou password incorretos. <a href='" . BASE_URL . "/user/login'>Tentar novamente</a>";
            }
        }
    }

    public function logout() {
    $_SESSION = array(); // Limpa todas as variáveis de sessão
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

        // Processar Formulário
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $bio = $_POST['bio'];
            
            // 1. Atualizar Texto
            $userModel->updateProfile($userId, $name, $bio);
            $message = "Perfil atualizado com sucesso!";
            $_SESSION['user_name'] = $name; // Atualizar sessão

            // 2. Upload de Imagem [RF11]
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['avatar']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    // Nome único para evitar conflitos
                    $newName = "user_" . $userId . "_" . time() . "." . $ext;
                    $dest = "../public/uploads/" . $newName;

                    // Criar pasta se não existir
                    if (!is_dir("../public/uploads")) mkdir("../public/uploads", 0777, true);

                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                        $userModel->updateAvatar($userId, $newName);
                        $message .= " Avatar carregado.";
                    }
                } else {
                    $message .= " Erro: Apenas imagens JPG, PNG ou GIF.";
                }
            }
        }

        $user = $userModel->findById($userId);
        $this->view('user/profile', ['user' => $user, 'message' => $message]);
    }

    // [RF03] Endpoint AJAX para validar email em tempo real
    public function check_email() {
        // Apenas aceita pedidos POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Ler o JSON enviado pelo JavaScript
            $data = json_decode(file_get_contents("php://input"));
            $email = $data->email ?? '';

            // Verificar na BD
            $userModel = new User();
            $exists = $userModel->emailExists($email);

            // Retornar JSON
            header('Content-Type: application/json');
            echo json_encode(['exists' => $exists]);
            exit;
        }
        
        // Se tentarem aceder diretamente via GET, redireciona
        header('Location: ' . BASE_URL . '/user/register');
        exit;
    }

    public function forgotPassword() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        // 1. Verificar se email existe
        // 2. Gerar token de recuperação e guardar na BD com validade
        // 3. Enviar email com link para definir nova password [cite: 19]
        echo "Se o email existir, receberá um link de recuperação.";
    } else {
        $this->view('user/forgot_password');
    }
}
}
?>