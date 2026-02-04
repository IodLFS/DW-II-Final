// Variável para controlar visualmente a vez
let currentTurnPlayerId = null;

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

// Enviar a carta para a API
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

        const data = await response.json();

        if (response.ok) {
            console.log("Jogada aceite!");
            fetchGameState(); // Atualiza logo o tabuleiro
        } else {
            alert("Erro: " + (data.error || "Jogada inválida"));
        }
    } catch (error) {
        console.error("Erro ao jogar:", error);
    }
}

function renderGame(data) {
    currentTurnPlayerId = data.current_turn;

    // 1. Trunfo
    document.getElementById('trump-card-display').innerText = convertCardToSymbol(data.trump.card);
    
    // 2. Estado (Info)
    const statusMsg = document.getElementById('status-msg');
    if (statusMsg) {
        statusMsg.innerText = (data.current_turn == <?php echo $_SESSION['user_id'] ?? 0; ?>) ? "A TUA VEZ!" : "À espera...";
    }

    // 3. Mesa (Cartas jogadas) - [NOVO]
    const tableDiv = document.getElementById('table-center');
    tableDiv.innerHTML = ''; 
    
    if (data.table_cards && data.table_cards.length > 0) {
        data.table_cards.forEach(play => {
            const cardEl = document.createElement('div');
            cardEl.className = `card ${isRed(play.card) ? 'red' : 'black'}`;
            // Posicionar no centro
            cardEl.style.position = 'absolute'; 
            cardEl.style.marginLeft = (Math.random() * 20 - 10) + 'px'; // Ligeira rotação/posição aleatória
            cardEl.innerText = convertCardToSymbol(play.card);
            tableDiv.appendChild(cardEl);
        });
    }

    // 4. Minha Mão
    const handContainer = document.getElementById('my-hand');
    handContainer.innerHTML = ''; 

    data.my_hand.forEach(cardCode => {
        const card = document.createElement('div');
        card.className = `card ${isRed(cardCode) ? 'red' : 'black'}`;
        card.innerText = convertCardToSymbol(cardCode);
        
        // Ao clicar, chama a função REAL de jogar
        card.onclick = () => playCard(cardCode);
        
        handContainer.appendChild(card);
    });
}

// Auxiliares
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

// Loop
fetchGameState();
setInterval(fetchGameState, 2000); // 2 segundos para ser mais rápido