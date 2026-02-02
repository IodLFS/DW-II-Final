<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?></title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; }
        .btn { padding: 10px 20px; background: #333; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1><?php echo $titulo; ?></h1>
    <p>O melhor jogo de cartas em PHP e Laravel.</p>
    <br>
    <a href="<?php echo BASE_URL; ?>/user/register" class="btn">Criar Conta</a>
    <a href="<?php echo BASE_URL; ?>/user/login" class="btn">Entrar</a>
</body>
</html>