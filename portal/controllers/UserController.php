<?php
require_once '../models/User.php';

class UserController extends Controller {

    public function register() {
        $this->view('user/register');
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $data = [
                'name'     => trim($_POST['name']),
                'username' => trim($_POST['username']),
                'email'    => trim($_POST['email']),
                'password' => $_POST['password']
            ];

            if (empty($data['name']) || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                die("Por favor preencha todos os campos."); 
            }

            $userModel = new User();

            if ($userModel->emailExists($data['email'])) {
                die("Erro: Este email já está registado.");
            }
            if ($userModel->usernameExists($data['username'])) {
                die("Erro: Este nome de utilizador já existe.");
            }

            if ($userModel->create($data)) {
                echo "Conta criada com sucesso! <a href='" . BASE_URL . "/user/login'>Fazer Login</a>";
            } else {
                die("Erro ao criar conta.");
            }
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

                if ($httpCode == 200 && isset($apiData['access_token'])) {
                    $_SESSION['jwt_token'] = $apiData['access_token'];
                } else {
                    $_SESSION['api_error'] = "Aviso: Não foi possível conectar ao motor de jogo.";
                }
                
                header('Location: ' . BASE_URL . '/lobby');
                exit;

            } else {
                echo "Email ou password incorretos. <a href='" . BASE_URL . "/user/login'>Tentar novamente</a>";
            }
        }
    }

    public function logout() {
        session_destroy(); 
        header('Location: ' . BASE_URL . '/user/login');
        exit;
    }
}
?>