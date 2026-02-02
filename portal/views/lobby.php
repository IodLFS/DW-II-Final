<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Lobby - Sueca Online</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f6f8; max-width: 800px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn-logout { color: #dc3545; text-decoration: none; font-weight: bold; border: 1px solid #dc3545; padding: 5px 10px; border-radius: 4px; }
        
        .create-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .create-form { display: flex; gap: 10px; }
        input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }

        .rooms-list { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; font-weight: 600; color: #555; }
        tr:hover { background-color: #f1f1f1; }
        .status-badge { background-color: #e2e6ea; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; }
        .btn-join { text-decoration: none; color: white; background-color: #007bff; padding: 5px 10px; border-radius: 4px; font-size: 0.9em; }
    </style>
</head>
<body>

    <header>
        <div>
            <h1>Sueca Online ♣️</h1>
            <small>Bem-vindo, <strong><?php echo $user_name; ?></strong></small>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/user/logout" class="btn-logout">Sair</a>
        </div>
    </header>

    <div class="create-section">
        <h3>Criar Nova Sala</h3>
        <form action="<?php echo BASE_URL; ?>/game/store" method="POST" class="create-form">
            <input type="text" name="room_name" placeholder="Nome da sala (ex: Mesa dos Amigos)" required>
            <button type="submit">Criar +</button>
        </form>
    </div>

    <div class="rooms-list">
        <table>
            <thead>
                <tr>
                    <th>Nome da Sala</th>
                    <th>Criador</th>
                    <th>Jogadores</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rooms) > 0): ?>
                    <?php foreach($rooms as $room): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['name']); ?></td>
                            <td><?php echo htmlspecialchars($room['creator_name']); ?></td>
                            <td><?php echo $room['player_count']; ?>/4</td>
                            <td>
                                <?php if($room['player_count'] < 4): ?>
                                    <a href="<?php echo BASE_URL; ?>/game/join/<?php echo $room['id']; ?>" class="btn-join">Entrar</a>
                                <?php else: ?>
                                    <span class="status-badge">Cheia</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">
                            Não há salas criadas. Sê o primeiro a criar uma!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>