<?php
class Controller {
    
    protected function view($view, $data = []) {

        extract($data); 

        if (file_exists("../views/$view.php")) {
            require_once "../views/$view.php";
        } else {
            die("A view $view não existe.");
        }
    }
}