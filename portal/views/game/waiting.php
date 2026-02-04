<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5"> 
    <title>Sala de Espera</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 20px; background-color: #2c3e50; color: white; }
        .container { max-width: 600px; margin: 0 auto; }
        .player-list { display: flex; justify-content: center; gap: 20px; margin-top: 40px; }
        .player-card { background: white; color: #333; padding: 20px; border-radius: 10px; width: 100px; }
        .seat { font-weight: bold; color: #888; margin-bottom: 5px; display: block; }
        .btn-start { background-color: #27ae60; color: white; padding: 15px 30px; border: none; font-size: 18px; cursor: pointer; border-radius: 5px; margin-top: 30px; text-decoration: none; display: inline-block; }
        .btn-start.disabled { background-color: #95a5a6; cursor: not-allowed; pointer-events: none; }
        .btn-back { color: #bdc3c7; text-decoration: none; margin-top: 20px; display: block; }
    </style>
</head>
<script>
    async function startGame() {
        const token = "<?php echo $_SESSION['jwt_token']; ?>"; 
        
        const response = await fetch('<?php echo API_BASE_URL; ?>/api/games/<?php echo $game['id']; ?>/start', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token }
        });

        if (response.ok) {
            window.location.href = "<?php echo BASE_URL; ?>/game/play/<?php echo $game['id']; ?>";
        } else {
            alert("Erro ao iniciar!");
        }
    }
    
    setTimeout(() => {
        location.reload(); 
    }, 5000);
    
    <?php if ($game['status'] === 'started'): ?>
        window.location.href = "<?php echo BASE_URL; ?>/game/play/<?php echo $game['id']; ?>";
    <?php endif; ?>
</script>

<button onclick="startGame()" class="btn-start">Iniciar Jogo!</button>
<body>

<div class="container">
    <h1>Sala: <?php echo htmlspecialchars($game['name']); ?></h1>
    <p>A aguardar jogadores... (<?php echo count($players); ?>/4)</p>

    <div class="player-list">
        <?php foreach ($players as $player): ?>
            <div class="player-card">
                <span class="seat">Cadeira <?php echo $player['seat_index']; ?></span>
                <div class="avatar">👤</div>
                <strong><?php echo htmlspecialchars($player['username']); ?></strong>
            </div>
        <?php endforeach; ?>
        
        <?php for($i = count($players); $i < 4; $i++): ?>
            <div class="player-card" style="opacity: 0.5;">
                <span class="seat">Vazio</span>
                <div class="avatar">Wait</div>
                <strong>...</strong>
            </div>
        <?php endfor; ?>
    </div>

    <?php if ($game['creator_id'] == $user_id): ?>
        <br>
        <?php if (count($players) == 4): ?>
            <a href="#" class="btn-start">Iniciar Jogo!</a>
        <?php else: ?>
            <a href="#" class="btn-start disabled">Aguardar 4 Jogadores</a>
        <?php endif; ?>
    <?php else: ?>
        <br><p>A aguardar que o dono inicie a partida...</p>
    <?php endif; ?>

    <a href="<?php echo BASE_URL; ?>/lobby" class="btn-back">Voltar ao Lobby</a>
</div>

</body>
</html>