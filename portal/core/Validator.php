<?php
/**
 * Classe de validação centralizada
 * [RNF05] Implementa validações de entrada de forma segura
 */
class Validator {
    protected $errors = [];

    public function validateEmail($email) {
        if (empty($email)) {
            $this->errors[] = "Email é obrigatório.";
            return false;
        }
        
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Email inválido.";
            return false;
        }
        return $email;
    }

    public function validatePassword($password) {
        if (empty($password)) {
            $this->errors[] = "Password é obrigatória.";
            return false;
        }
        if (strlen($password) < 6) {
            $this->errors[] = "Password deve ter pelo menos 6 caracteres.";
            return false;
        }
        return true;
    }

    public function validateUsername($username) {
        if (empty($username)) {
            $this->errors[] = "Nome de utilizador é obrigatório.";
            return false;
        }
        if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username)) {
            $this->errors[] = "Nome de utilizador deve ter 3-20 caracteres (letras, números, _ e -).";
            return false;
        }
        return true;
    }

    public function validateName($name) {
        if (empty($name)) {
            $this->errors[] = "Nome é obrigatório.";
            return false;
        }
        if (strlen($name) < 2 || strlen($name) > 100) {
            $this->errors[] = "Nome deve ter entre 2 e 100 caracteres.";
            return false;
        }
        return true;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function clearErrors() {
        $this->errors = [];
    }
}
?>
