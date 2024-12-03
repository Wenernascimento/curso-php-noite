<?php
// Conexão com o banco de dados
$pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProduto = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];
    
    // Consulta o produto para pegar o preço
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->bindParam(':id', $idProduto, PDO::PARAM_INT);
    $stmt->execute();
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifica se o produto existe e se há estoque suficiente
    if ($produto && $produto['quantidade'] >= $quantidade) {
        // Calcula o total da venda
        $total = $produto['preco'] * $quantidade;
        
        // Registra a venda
        $stmt = $pdo->prepare("INSERT INTO vendas (id_produto, quantidade, total) VALUES (:id_produto, :quantidade, :total)");
        $stmt->bindParam(':id_produto', $idProduto, PDO::PARAM_INT);
        $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':total', $total, PDO::PARAM_STR);
        $stmt->execute();
        
        // Atualiza o estoque
        $novoEstoque = $produto['quantidade'] - $quantidade;
        $stmt = $pdo->prepare("UPDATE produtos SET quantidade = :quantidade WHERE id = :id");
        $stmt->bindParam(':quantidade', $novoEstoque, PDO::PARAM_INT);
        $stmt->bindParam(':id', $idProduto, PDO::PARAM_INT);
        $stmt->execute();
        
        echo "<p style='color: green;'>Venda registrada com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>Erro: Produto não encontrado ou estoque insuficiente.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: linear-gradient(45deg, #87CEEB, #B0E0E6, #E0FFFF, #ADD8E6);
            background-size: 300% 300%;
            animation: gradientBG 8s ease infinite;
            color: #444;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        h1 {
            color: #fff;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            margin: 0 auto;
        }

        label {
            font-weight: bold;
            font-size: 14px;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        select, input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        select {
            font-family: 'Georgia', serif; /* Estilo aplicado ao dropdown */
            font-size: 16px;
            font-weight: bold;
            color: #2F4F4F; /* Cor escura (cinza ardósia) */
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        p {
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Registrar Venda</h1>
    <form method="POST">
        <label for="produto_id">Produto:</label>
        <select name="produto_id" required>
            <option value="">Selecione um produto</option>
            <?php
            // Busca todos os produtos para exibir no formulário
            $stmt = $pdo->query("SELECT * FROM produtos");
            while ($produto = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$produto['id']}'>{$produto['nome']}</option>";
            }
            ?>
        </select>

        <label for="quantidade">Quantidade:</label>
        <input type="number" name="quantidade" min="1" required>

        <button type="submit">Registrar Venda</button>
    </form>
</body>
</html>
