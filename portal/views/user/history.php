<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Jogos</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f6f8; max-width: 800px; margin: 0 auto; }
        h2 { border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

    <a href="<?php echo BASE_URL; ?>/lobby" class="back-link">← Voltar ao Lobby</a>
    <h2>O Meu Histórico</h2>

    <?php if (count($games) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Sala</th>
                    <th>Pontuação Final</th>
                    <th>Vencedor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($games as $game): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($game['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($game['name']); ?></td>
                        <td>
                            Nós: <?php echo $game['score_team_a']; ?> - 
                            Eles: <?php echo $game['score_team_b']; ?>
                        </td>
                        <td>
                            <?php 
                                // Exemplo simples de lógica de vencedor
                                if ($game['score_team_a'] > $game['score_team_b']) echo "Equipa A (Pares)";
                                elseif ($game['score_team_b'] > $game['score_team_a']) echo "Equipa B (Ímpares)";
                                else echo "Empate";
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Ainda não tens jogos terminados.</p>
    <?php endif; ?>

</body>
</html>