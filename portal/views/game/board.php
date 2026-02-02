<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Mesa de Jogo</title>
    <style>
        body { background-color: #2e7d32; font-family: sans-serif; overflow: hidden; height: 100vh; margin: 0; display: flex; justify-content: center; align-items: center; }
        
        #game-table { position: relative; width: 800px; height: 600px; border: 5px solid #1b5e20; border-radius: 50px; background-color: #388e3c; box-shadow: inset 0 0 20px rgba(0,0,0,0.5); }
        
        .info-panel { position: absolute; top: 10px; left: 10px; color: white; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 8px; }

        #my-hand { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: -30px; }
        .card { width: 70px; height: 100px; background: white; border-radius: 5px; border: 1px solid #ccc; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 1.2em; cursor: pointer; transition: transform 0.2s; box-shadow: 2px 2px 5px rgba(0,0,0,0.3); margin-right: 5px; }
        .card:hover { transform: translateY(-15px); z-index: 10; }
        .card.red { color: red; } .card.black { color: black; }

        #table-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; }
        
        #trump-indicator { position: absolute; top: 20px; right: 20px; background: white; padding: 10px; border-radius: 5px; text-align: center; }
    </style>
</head>
<body>

<div id="game-table">
    <div class="info-panel">
        <div>Sala: <strong><?php echo $game_id; ?></strong></div>
        <div id="status-msg">A carregar...</div>
    </div>

    <div id="trump-indicator">
        <div style="font-size: 12px;">TRUNFO</div>
        <strong id="trump-card-display">?</strong>
    </div>

    <div id="table-center">
        </div>

    <div id="my-hand">
        </div>
</div>

<script>
    const API_URL = "http://127.0.0.1:8000/api";
    const GAME_ID = <?php echo $game_id; ?>;
    const USER_TOKEN = "<?php echo $_SESSION['jwt_token']; ?>"; 
</script>

<script src="<?php echo BASE_URL; ?>/js/game.js"></script>

</body>
</html>