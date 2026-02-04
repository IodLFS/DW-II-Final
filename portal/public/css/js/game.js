// public/js/game.js

async function fetchGameState() {
    try {
        const response = await fetch(`${API_URL}/game/${GAME_ID}/state`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${USER_TOKEN}`,
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            renderGame(data);
        }
    } catch (error) {
        console.error("Erro de rede:", error);
    }
}

// NOVA FUNÇÃO: Envia a carta para a API
async function playCard(cardCode) {
    try {
        const response = await fetch(`${API_URL}/game/${GAME_ID}/play`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${USER_TOKEN}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ card: cardCode })
        });

        if (response.ok) {
            fetchGameState(); // Atualiza a mesa imediatamente
        } else {
            const data = await response.json();
            alert("Erro: " + (data.error || "Não foi possível jogar"));
        }
    } catch (error) {
        console.error("Erro ao jogar:", error);
    }
}

function renderGame(data) {
    // [RF33] VERIFICAÇÃO DE FIM DE JOGO
    if (data.status === 'finished') {
        const gameContainer = document.getElementById('game-table');
        
        // Substitui todo o conteúdo da mesa pelo ecrã de vitória
        gameContainer.innerHTML = `
            <div style="text-align: center; color: white; padding-top: 100px;">
                <h1 style="font-size: 50px;">FIM DE JOGO! 🏆</h1>
                <h2>Vencedores: <span style="color: yellow;">${data.winner || 'Empate'}</span></h2>
                <div style="font-size: 24px; margin: 20px 0;">
                    <p>Nós: ${data.scores.team_A} pts | Eles: ${data.scores.team_B} pts</p>
                </div>
                <button onclick="window.location.href='${BASE_URL}/lobby'" 
                        style="padding: 15px 30px; font-size: 20px; background: #d32f2f; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Voltar ao Lobby
                </button>
            </div>
        `;
        return; // Pára a execução para não desenhar mais cartas
    }

    // ... (O resto do código da função renderGame continua igual aqui para baixo) ...
    // 1. Info e Pontos...
    // 2. Trunfo...

    // Desenha Minha Mão
    const handContainer = document.getElementById('my-hand');
    handContainer.innerHTML = ''; 

    data.my_hand.forEach(cardCode => {
        const card = document.createElement('div');
        card.className = `card ${isRed(cardCode) ? 'red' : 'black'}`;
        card.innerText = convertCardToSymbol(cardCode);
        
        // Clicar chama a função real
        card.onclick = () => playCard(cardCode);
        
        handContainer.appendChild(card);
    });
    
    // Mostra estado
    const statusMsg = document.getElementById('status-msg');
    if(statusMsg) {
        // Assume que a variável PHP USER_ID não está disponível aqui diretamente, 
        // mas podes inferir se a API retornar o teu ID ou apenas testar visualmente.
        statusMsg.innerText = "Turno do Jogador ID: " + data.current_turn;
    }
}

function convertCardToSymbol(code) {
    if (!code) return '?';
    const suits = {'h': '♥', 'd': '♦', 's': '♠', 'c': '♣'};
    const rank = code.slice(0, -1);
    const suit = code.slice(-1);
    return `${rank} ${suits[suit] || suit}`;
}

function isRed(code) {
    return code && (code.includes('h') || code.includes('d'));
}

function showNotification(message, isError = true) {
    // Cria o elemento se não existir
    let toast = document.getElementById('game-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'game-toast';
        toast.style.position = 'absolute';
        toast.style.top = '20px';
        toast.style.left = '50%';
        toast.style.transform = 'translateX(-50%)';
        toast.style.padding = '10px 20px';
        toast.style.borderRadius = '5px';
        toast.style.color = 'white';
        toast.style.fontWeight = 'bold';
        toast.style.zIndex = '1000';
        toast.style.transition = 'opacity 0.5s';
        document.body.appendChild(toast);
    }

    // Configura a mensagem
    toast.style.backgroundColor = isError ? 'rgba(255, 0, 0, 0.8)' : 'rgba(0, 128, 0, 0.8)';
    toast.innerText = message;
    toast.style.opacity = '1';

    // Desaparece após 3 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
    }, 3000);
}