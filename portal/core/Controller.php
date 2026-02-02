<?php
class Controller {
    public function view($viewName, $data = []) {
        extract($data);

        $filename = "../views/" . $viewName . ".php";
        if (file_exists($filename)) {
            require_once $filename;
        } else {
            die("View '$viewName' não encontrada!");
        }
    }
}
?>