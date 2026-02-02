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
        } else {
            console.error("Erro ao obter estado do jogo");
        }
    } catch (error) {
        console.error("Erro de rede:", error);
    }
}

function renderGame(data) {
    document.getElementById('trump-card-display').innerText = data.trump.card;

    const handContainer = document.getElementById('my-hand');
    handContainer.innerHTML = ''; 

    data.my_hand.forEach(cardCode => {
        const card = document.createElement('div');
        card.className = `card ${isRed(cardCode) ? 'red' : 'black'}`;
        card.innerText = convertCardToSymbol(cardCode);
        
        // Clique para jogar (Futuro)
        card.onclick = () => playCard(cardCode);
        
        handContainer.appendChild(card);
    });
}

function convertCardToSymbol(code) {
    const suits = {'h': '♥', 'd': '♦', 's': '♠', 'c': '♣'};
    const rank = code.slice(0, -1);
    const suit = code.slice(-1);
    return `${rank} ${suits[suit]}`;
}

function isRed(code) {
    return code.includes('h') || code.includes('d');
}

function playCard(card) {
    alert(`Tentaste jogar: ${card}. (Lógica de jogada a implementar no próximo passo)`);
}

fetchGameState();
setInterval(fetchGameState, 3000); 