<?php
// Conexão com o banco de dados
try {
    $pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Processamento da venda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtos = $_POST['produto_id'];  // Array de IDs de produtos
    $quantidades = $_POST['quantidade'];  // Array de quantidades
    $formaPagamento = $_POST['forma_pagamento'];  // Forma de pagamento
    $valorPago = $_POST['valor_pago'];  // Valor pago (único para todos os produtos)

    // Verifica se os dados são válidos
    if (empty($produtos) || empty($quantidades) || !$formaPagamento) {
        echo "<p style='color: red;'>Erro: Dados inválidos.</p>";
        exit;
    }

    try {
        // Inicia transação
        $pdo->beginTransaction();
        
        $totalVenda = 0;

        // Processa cada produto e calcula o total
        foreach ($produtos as $index => $idProduto) {
            $quantidade = $quantidades[$index];

            // Busca o produto para pegar o preço e o estoque
            $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
            $stmt->bindValue(':id', $idProduto, PDO::PARAM_INT);
            $stmt->execute();
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifica se o produto existe e se há estoque suficiente
            if ($produto && $produto['quantidade'] >= $quantidade) {
                // Calcula o total da venda para esse produto
                $totalVenda += $produto['preco'] * $quantidade;

                // Registra a venda
                $stmt = $pdo->prepare("INSERT INTO vendas (id_produto, quantidade, total, data_venda, forma_pagamento) 
                                       VALUES (:id_produto, :quantidade, :total, NOW(), :forma_pagamento)");
                $stmt->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
                $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
                $stmt->bindValue(':total', $produto['preco'] * $quantidade, PDO::PARAM_STR);
                $stmt->bindValue(':forma_pagamento', $formaPagamento, PDO::PARAM_STR);
                $stmt->execute();

                // Atualiza o estoque
                $novoEstoque = $produto['quantidade'] - $quantidade;
                $stmt = $pdo->prepare("UPDATE produtos SET quantidade = :quantidade WHERE id = :id");
                $stmt->bindValue(':quantidade', $novoEstoque, PDO::PARAM_INT);
                $stmt->bindValue(':id', $idProduto, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                echo "<p style='color: red;'>Erro: Produto " . ($index + 1) . " não encontrado ou estoque insuficiente.</p>";
                exit;
            }
        }

        // Verifica se o valor pago é suficiente
        if ($valorPago < $totalVenda && $formaPagamento == 'dinheiro') {
            echo "<p style='color: red;'>Erro: Valor pago insuficiente para o total da venda.</p>";
            exit;
        }

        // Se o pagamento for em dinheiro, calcula o troco
        $troco = 0;
        if ($formaPagamento == 'dinheiro') {
            $troco = $valorPago - $totalVenda;
            echo "<p style='color: green;'><strong>Troco: R$ " . number_format($troco, 2, ',', '.') . "</strong></p>";
        }

        // Commit da transação
        $pdo->commit();

        echo "<p style='color: green;'><strong>✔</strong> Venda registrada com sucesso!</p>";
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
            background: rgba(141, 134, 134, 0.8);
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            margin: 0 auto;
        }

        label {
            font-weight: bold;
            font-size: 15px;
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
            background-color:rgb(22, 17, 17);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 05px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
            width: 49%;
        }

        button:hover {
            background-color:rgb(86, 42, 218);
        }

        p {
            text-align: center;
            font-size: 14px;
        }

        #totalVenda {
            font-weight: bold;
            color: #2F4F4F;
            font-size: 20px;
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
    <form method="POST" id="vendaForm">
        <div id="produtos">
            <!-- Campo inicial para selecionar um produto -->
            <div class="produto-item">
                <label for="produto_id[]">Produto:</label>
                <select name="produto_id[]" required class="produto-select">
                    <option value="">Selecione um produto</option>
                    <?php
                    // Busca todos os produtos para exibir no formulário
                    $stmt = $pdo->query("SELECT * FROM produtos");
                    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($produtos) {
                        foreach ($produtos as $produto) {
                            echo "<option value='{$produto['id']}' data-preco='{$produto['preco']}'>{$produto['nome']} - R$ {$produto['preco']}</option>";
                        }
                    } else {
                        echo "<option value='' disabled>Sem produtos cadastrados</option>";
                    }
                    ?>
                </select>

                <label for="quantidade[]">Quantidade:</label>
                <input type="number" name="quantidade[]" min="1" required class="quantidade-input">
                <span class="preco-item">R$ 0,00</span>
            </div>
        </div>

        <label for="forma_pagamento">Forma de Pagamento:</label>
        <select name="forma_pagamento" id="forma_pagamento" required>
            <option value="">Selecione a forma de pagamento</option>
            <option value="debito">Débito</option>
            <option value="credito">Crédito</option>
            <option value="pix">Pix</option>
            <option value="dinheiro">Dinheiro</option>
        </select>

        <div id="valor_pago_container" style="display: none;">
            <label for="valor_pago">Valor Pago:</label>
            <input type="number" name="valor_pago" step="0.01" min="0" placeholder="Digite o valor pago">
        </div>

        <p><strong>Total da Venda: R$ <span id="totalVenda">0,00</span></strong></p>

        <button type="button" id="addProduto">Adicionar outro produto</button>
        <button type="submit">Registrar Venda</button>
    </form>

    <script>
        // Atualiza o preço total de um item quando a quantidade ou o produto for alterado
        function atualizarPrecoItem() {
            const produtoSelects = document.querySelectorAll('.produto-select');
            const quantidadeInputs = document.querySelectorAll('.quantidade-input');
            const precoItems = document.querySelectorAll('.preco-item');
            let totalVenda = 0;

            produtoSelects.forEach((produtoSelect, index) => {
                const quantidade = quantidadeInputs[index].value;
                const precoUnitario = parseFloat(produtoSelect.options[produtoSelect.selectedIndex].getAttribute('data-preco'));
                
                if (quantidade && precoUnitario) {
                    const precoTotalItem = precoUnitario * quantidade;
                    precoItems[index].textContent = `R$ ${precoTotalItem.toFixed(2).replace('.', ',')}`;
                    totalVenda += precoTotalItem;
                } else {
                    precoItems[index].textContent = `R$ 0,00`;
                }
            });

            document.getElementById('totalVenda').textContent = totalVenda.toFixed(2).replace('.', ',');
        }

        // Adiciona mais produtos ao formulário
        document.getElementById('addProduto').addEventListener('click', function() {
            let novoProduto = document.querySelector('.produto-item').cloneNode(true);
            document.getElementById('produtos').appendChild(novoProduto);
            atualizarPrecoItem();
        });

        // Atualiza o total sempre que a quantidade ou o produto for alterado
        document.getElementById('produtos').addEventListener('change', atualizarPrecoItem);
        document.getElementById('produtos').addEventListener('input', atualizarPrecoItem);

        // Exibe o campo para o valor pago quando a forma de pagamento for "dinheiro"
        document.getElementById('forma_pagamento').addEventListener('change', function() {
            var valorPagoContainer = document.getElementById('valor_pago_container');
            if (this.value === 'dinheiro') {
                valorPagoContainer.style.display = 'block';
            } else {
                valorPagoContainer.style.display = 'none';
            }
        });
    </script>

    <p>
        <a href="index.php" style="background-color:rgb(19, 231, 15); padding: 10px 20px; border-radius: 5px; color: white; font-weight: bold; text-decoration: none; margin-right: 10px;">Voltar para Início</a>
        <a href="gasto.php" style="background-color:rgb(128, 34, 235); padding: 10px 20px; border-radius: 5px; color: white; font-weight: bold; text-decoration: none;">Gasto Saída</a>
    </p>
</body>
</html>
