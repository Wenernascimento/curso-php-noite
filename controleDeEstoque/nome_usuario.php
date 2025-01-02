<?php
// Conexão com o banco de dados
try {
    $pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', ''); // Ajuste conforme seu banco
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Verificando se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se as variáveis 'nome', 'email' e 'senha' foram enviadas
    if (isset($_POST['nome'], $_POST['email'], $_POST['senha'])) {
        $nome = trim($_POST['nome']);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $senha = trim($_POST['senha']);

        // Verificar se o e-mail é válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "E-mail inválido!";
        } else {
            // Verificando se o e-mail já existe no banco de dados
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo "Este e-mail já está cadastrado!";
            } else {
                // Fazendo o hash da senha antes de salvar
                $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

                // Inserindo o novo usuário no banco de dados
                $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
                $stmtInsert->bindParam(':nome', $nome);
                $stmtInsert->bindParam(':email', $email);
                $stmtInsert->bindParam(':senha', $senhaHash);

                if ($stmtInsert->execute()) {
                    echo "Cadastro realizado com sucesso!";
                    // Redireciona para a página de login
                    header('Location: login.php');
                    exit();
                } else {
                    echo "Erro ao cadastrar usuário!";
                }
            }
        }
    } else {
        echo "Preencha todos os campos!";
    }
}
?>

<!-- Formulário de Cadastro -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta</title>
    <style>
        /* Estilos do plano de fundo e layout */
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('https://source.unsplash.com/1600x900/?abstract'); /* Plano de fundo bonito */
            background-size: cover;
            background-position: center;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="email"], input[type="password"], input[type="text"] {
            background-color: #f9f9f9;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Criar Conta</h2>

        <!-- Exibindo erro, se houver -->
        <?php if (isset($erroCadastro)): ?>
            <div class="error-message"><?= htmlspecialchars($erroCadastro) ?></div>
        <?php endif; ?>

        <!-- Formulário de Cadastro -->
        <form method="POST">
            <label for="nome">Nome Completo:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required>

            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>

            <button type="submit">Cadastrar</button>
        </form>

        <!-- Link para redirecionar para a página de login -->
        <div class="login-link">
            <p>Já tem uma conta? <a href="login.php">Entrar</a></p>
        </div>
    </div>

</body>
</html>
