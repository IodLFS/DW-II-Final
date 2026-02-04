<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registo - Sueca Online</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f0f2f5; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #218838; }
        .login-link { text-align: center; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const emailInput = document.querySelector('input[name="email"]');
    const submitBtn = document.querySelector('button[type="submit"]'); // O botão de registar
    
    // 1. Criar o elemento para a mensagem de erro/sucesso
    const msgSpan = document.createElement("span");
    msgSpan.style.fontWeight = "bold";
    msgSpan.style.fontSize = "0.9em";
    msgSpan.style.display = "block";
    msgSpan.style.marginTop = "5px";
    
    // Insere o span logo a seguir ao input do email
    emailInput.parentNode.insertBefore(msgSpan, emailInput.nextSibling);

    // 2. Adicionar o evento 'blur' (quando sai do campo)
    emailInput.addEventListener("blur", function() {
        const email = this.value;

        // Se estiver vazio ou for muito curto, não faz nada
        if(email.length < 5 || !email.includes('@')) {
            msgSpan.innerText = "";
            emailInput.style.borderColor = "#ddd";
            return;
        }

        // Feedback visual de "a carregar..."
        msgSpan.innerText = "🔄 A verificar...";
        msgSpan.style.color = "#666";

        // 3. Fazer o pedido AJAX (Fetch API) [RNF07]
        fetch('<?php echo BASE_URL; ?>/user/check_email', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                // EMAIL JÁ EXISTE
                msgSpan.innerText = "❌ Este email já está registado!";
                msgSpan.style.color = "red";
                emailInput.style.borderColor = "red";
                submitBtn.disabled = true; // Bloqueia o registo
            } else {
                // EMAIL DISPONÍVEL
                msgSpan.innerText = "✅ Email disponível.";
                msgSpan.style.color = "green";
                emailInput.style.borderColor = "green";
                submitBtn.disabled = false; // Desbloqueia
            }
        })
        .catch(err => {
            console.error("Erro na verificação:", err);
            msgSpan.innerText = "";
        });
    });
    
    // Limpar erro quando o utilizador começa a escrever de novo
    emailInput.addEventListener("input", function() {
        submitBtn.disabled = false;
        msgSpan.innerText = "";
        emailInput.style.borderColor = "#ddd";
    });
});
</script>
<body>

<div class="card">
    <h2>Criar Conta</h2>
    
    <form action="<?php echo BASE_URL; ?>/user/store" method="POST">
        
        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="name" required placeholder="Ex: João Silva">
        </div>

        <div class="form-group">
            <label>Nome de Utilizador</label>
            <input type="text" name="username" required placeholder="Ex: jsilva99">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="Ex: joao@email.com">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Registar</button>
    </form>

    <div class="login-link">
        Já tens conta? <a href="<?php echo BASE_URL; ?>/user/login">Entrar aqui</a>
    </div>
</div>

</body>
</html>