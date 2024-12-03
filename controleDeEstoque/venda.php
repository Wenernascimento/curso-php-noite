<?php
// Conexão com o banco de dados
try {
    $pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitização e validação das entradas
    $idProduto = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

    // Verifica se a quantidade e ID são válidos
    if (!$quantidade || !$idProduto) {
        echo "<p style='color: red;'>Erro: Dados inválidos.</p>";
        exit;
    }

    try {
        // Busca o produto para pegar o preço e o estoque
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->bindParam(':id', $idProduto, PDO::PARAM_INT);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se o produto existe e se há estoque suficiente
        if ($produto && $produto['quantidade'] >= $quantidade) {
            // Calcula o total da venda
            $total = $produto['preco'] * $quantidade;

            // Inicia transação
            $pdo->beginTransaction();

            // Registra a venda (podemos adicionar a data da venda)
            $stmt = $pdo->prepare("INSERT INTO vendas (id_produto, quantidade, total, data_venda) VALUES (:id_produto, :quantidade, :total, NOW())");
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

            // Commit
            $pdo->commit();

            echo "<p style='color: green;'><strong>✔</strong> Venda registrada com sucesso!</p>";
        } else {
            echo "<p style='color: red;'><strong>✘</strong> Erro: Produto não encontrado ou estoque insuficiente.</p>";
        }
    } catch (Exception $e) {
        // Rollback em caso de erro
        $pdo->rollBack();
        echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
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
            background-image: url('mercado1.png');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            color: #444;
        }

        h1 {
            color: #fff;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        form {
            background: rgba(255, 255, 255, 0.8);
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
            font-family: 'Georgia', serif;
            font-size: 16px;
            font-weight: bold;
            color: #2F4F4F;
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

        @media (max-width: 600px) {
            form {
                width: 90%;
            }

            h1 {
                font-size: 1.5em;
            }
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
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($produtos as $produto) {
                echo "<option value='{$produto['id']}' data-estoque='{$produto['quantidade']}' data-preco='{$produto['preco']}'>{$produto['nome']} - R$ {$produto['preco']}</option>";
            }
            ?>
        </select>

        <label for="quantidade">Quantidade:</label>
        <input type="number" name="quantidade" id="quantidade" min="1" required>

        <button type="submit">Registrar Venda</button>
    </form>

    <script>
        // Limitar a quantidade de acordo com o estoque disponível
        document.querySelector('select[name="produto_id"]').addEventListener('change', function() {
            var estoque = this.options[this.selectedIndex].getAttribute('data-estoque');
            var quantidadeInput = document.querySelector('input[name="quantidade"]');
            quantidadeInput.setAttribute('max', estoque);  // Define o valor máximo
            if (quantidadeInput.value > estoque) {
                quantidadeInput.value = estoque;  // Ajusta a quantidade caso o valor seja maior que o estoque
            }
        });
    </script>
</body>
</html>
