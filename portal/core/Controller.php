<?php
class Controller {
    protected $lang;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();

        // Define idioma padrão ou o escolhido pelo utilizador
        $langCode = $_SESSION['lang'] ?? 'pt';
        
        // Carrega o ficheiro correspondente
        $this->lang = include "../lang/{$langCode}.php";
    }

    protected function view($view, $data = []) {
        // Passa as traduções automaticamente para a vista
        $data['texts'] = $this->lang;
        require_once "../views/{$view}.php";
    }
}