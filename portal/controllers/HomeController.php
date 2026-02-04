<?php
class HomeController extends Controller {
    public function index() {

        $dados = [
            'titulo' => 'Bem-vindo à Sueca Online'
        ];


        $this->view('home', $dados);
    }
}

?>