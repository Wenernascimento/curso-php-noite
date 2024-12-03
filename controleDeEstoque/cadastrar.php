<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $validade = $_POST['validade'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];

    $stmt = $conn->prepare("INSERT INTO produtos (nome, validade, preco, quantidade) VALUES (:nome, :validade, :preco, :quantidade)");
    $stmt->execute(['nome' => $nome, 'validade' => $validade, 'preco' => $preco, 'quantidade' => $quantidade]);

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Produto</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f9c4d1, #a6e1ff);
            animation: gradientBackground 10s ease infinite;
            color: #333;
        }

        @keyframes gradientBackground {
            0% {
                background: linear-gradient(135deg, #f9c4d1, #a6e1ff);
            }
            50% {
                background: linear-gradient(135deg, #e3f4f0, #f0c1d9);
            }
            100% {
                background: linear-gradient(135deg, #f9c4d1, #a6e1ff);
            }
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            font-size: 2.2em;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 5px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        button {
            padding: 12px;
            font-size: 1em;
            border-radius: 8px;
            border: 1px solid #ccc;
            background-color: #fff;
            transition: 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 8px rgba(76, 175, 80, 0.6);
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastrar Produto</h1>
        <form method="POST">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="validade">Validade:</label>
            <input type="date" id="validade" name="validade" required>

            <label for="preco">Preço:</label>
            <input type="number" id="preco" step="0.01" name="preco" required>

            <label for="quantidade">Quantidade:</label>
            <input type="number" id="quantidade" name="quantidade" required>

            <button type="submit">Salvar</button>
        </form>
        <a class="back-link" href="index.php">Voltar</a>
    </div>
</body>
</html>
