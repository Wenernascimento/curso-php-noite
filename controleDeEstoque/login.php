<?php
// Configurações de banco de dados usando variáveis de ambiente (melhor para produção)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'estoque');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Função para criar a conexão com o banco de dados
function getDbConnection() {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        logError($e->getMessage()); // Log do erro
        die("Erro na conexão com o banco de dados. Tente novamente mais tarde.");
    }
}

// Função para autenticar o usuário
function authenticateUser($email, $senha) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        return true;
    }

    return false;
}

// Função para logar erros no arquivo de log
function logError($errorMessage) {
    $logFile = 'error_log.txt'; // Caminho do arquivo de log
    $currentDate = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$currentDate] - $errorMessage\n", FILE_APPEND);
}

// Processando o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificando se os campos foram preenchidos
    if (!empty($_POST['email']) && !empty($_POST['senha'])) {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL); // Sanitizando o e-mail
        $senha = $_POST['senha'];

        // Validando e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erroLogin = "E-mail inválido. Por favor, insira um e-mail válido.";
        } elseif (authenticateUser($email, $senha)) {
            // Login bem-sucedido, redireciona para a página principal
            header('Location: index.php');
            exit; // Para garantir que o código abaixo não seja executado
        } else {
            // E-mail ou senha incorretos
            $erroLogin = "E-mail ou senha incorretos!";
        }
    } else {
        // Campos não preenchidos corretamente
        $erroLogin = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>
   
   <style>
        /* Estilos básicos para a página de login */
        body {
            font-family: Arial, sans-serif;
            background-image: url('mercado4.jpg'); /* Caminho para a imagem de fundo (local ou online) */
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: rgba(173, 170, 172, 0.9); /* Fundo translúcido para melhorar a legibilidade */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(198, 182, 182, 0.1);
            width: 300px;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-container label {
            font-size: 14px;
            color: #333;
        }

        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .login-container button:hover {
            background-color: #45a049;
        }

        .error-message {
            color: #ff0000;
            background-color: #fdd;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #f00;
        }

        .register-link {
            display: block;
            text-align: center;
            margin-top: 15px;
        }

        .register-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-container">
  
   
    <h2>Mercearia Rapadura 
        Login</h2>

        <!-- Exibindo erro, se houver -->
        <?php if (isset($erroLogin)): ?>
            <div class="error-message"><?= htmlspecialchars($erroLogin) ?></div>
        <?php endif; ?>

        <!-- Formulário de Login -->
        <form method="POST">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">

            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>

            <button type="submit">Entrar</button>
        </form>

        <!-- Link para registro de novo usuário -->
        <div class="register-link">
            <p>Não tem uma conta? <a href="nome_usuario.php">Registre-se</a></p>
        </div>
    </div>

</body>
</html>
