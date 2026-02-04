<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>O Meu Perfil</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f6f8; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .avatar-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #eee; margin-bottom: 15px; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .alert { color: green; margin-bottom: 15px; }
        .back-link { display: block; margin-top: 20px; color: #666; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <h2>Editar Perfil</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div style="text-align: center;">
            <?php 
                $avatar = $user['avatar'] ? BASE_URL . '/../uploads/' . $user['avatar'] : 'https:
            ?>
            <img src="<?php echo $avatar; ?>" class="avatar-preview" alt="Avatar">
            <br>
            <label>Alterar Foto:</label>
            <input type="file" name="avatar">
        </div>

        <label>Nome:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

        <label>Biografia:</label>
        <textarea name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

        <button type="submit">Guardar Alterações</button>
    </form>

    <div class="stats-section">
    <h3>Estatísticas de Jogo (Brevemente)</h3>
    <p>Jogos Jogados: -</p>
    <p>Vitórias/Derrotas: - / -</p>
</div>

    <a href="<?php echo BASE_URL; ?>/lobby" class="back-link">← Voltar ao Lobby</a>
</div>

</body>
</html>