<?php
// Iniciar sessão para utilizar o controle de erro e garantir que o código sempre esteja seguro
session_start();

// Inclui o arquivo de configuração do banco de dados
require_once 'db.php';

// Função para obter todos os produtos do banco de dados de maneira segura
function obterProdutos($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM produtos");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []; // Retorna array vazio se nenhum dado for encontrado
    } catch (PDOException $e) {
        error_log("Erro ao obter produtos: " . $e->getMessage());
        return []; // Retorna array vazio em caso de erro
    }
}

// Inicializa a variável $produtos como um array vazio
$produtos = obterProdutos($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Estoque</title>
    <style>
        /* Gradiente animado no fundo */
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

        a {
            text-decoration: none;
            color: white;
            background-color: #4CAF50;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        a:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        th, td {
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #4682B4;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.7);
        }

        tr:hover {
            background-color: rgba(240, 255, 240, 0.9);
            transition: background-color 0.3s;
        }

        .status {
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .status.red {
            color: #ff4c4c;
        }

        .status.orange {
            color: #ff9800;
        }

        .status.green {
            color: #4CAF50;
        }

        /* Botões de Ações */
        .actions a {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .actions a:first-child {
            background-color: #1E90FF;
            color: white;
        }

        .actions a:first-child:hover {
            background-color: #1C86EE;
            color: #f0f0f0;
        }

        .actions a:last-child {
            background-color: #FF6347;
            color: white;
        }

        .actions a:last-child:hover {
            background-color: #FF4500;
            color: #fffafa;
        }

        /* Estilo para os nomes dos produtos */
        td.nome-produto {
            font-family: 'Georgia', serif; /* Fonte estilizada */
            font-size: 18px; /* Tamanho maior */
            font-weight: bold; /* Negrito */
            font-style: italic; /* Itálico */
            color: #2F4F4F; /* Cor escura (cinza ardósia) */
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2); /* Sombra para destacar */
            text-transform: capitalize; /* Primeira letra maiúscula */
        }
    </style>
</head>
<body>
    <h1>Controle de Estoque</h1>
    <a href="cadastrar.php">Cadastrar Produto</a>

    <?php if (!empty($produtos)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Validade</th>
                <th>Preço Unitário</th>
                <th>Quantidade</th>
                <th>Valor de Venda</th>
                <th>Lucro Unitário</th>
                <th>Porcentagem de Lucro</th>
                <th>Total de Custo</th>
                <th>Total de Venda</th>
                <th>Total de Lucro</th>
                <th>Dízimo</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $produto): ?>
                <?php
                // Definindo os cálculos
                $preco = $produto['preco']; // Preço unitário
                $quantidade = $produto['quantidade']; // Quantidade em estoque
                $valorVenda = $preco * 1.2; // Exemplo de preço de venda com 20% de markup
                $lucroUnitario = $valorVenda - $preco; // Lucro unitário
                $porcentagemLucro = ($lucroUnitario / $preco) * 100; // Porcentagem de lucro
                $totalCusto = $preco * $quantidade; // Total de custo
                $totalVenda = $valorVenda * $quantidade; // Total de venda
                $totalLucro = $totalVenda - $totalCusto; // Total de lucro
                $dizimo = $totalLucro * 0.1; // Dízimo (10% do lucro)
                ?>

                <tr>
                    <td><?= htmlspecialchars($produto['id']) ?></td>
                    <td class="nome-produto"><?= htmlspecialchars($produto['nome']) ?></td>
                    <td><?= htmlspecialchars($produto['validade']) ?></td>
                    <td>R$<?= number_format($preco, 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($produto['quantidade']) ?></td>
                    <td>R$<?= number_format($valorVenda, 2, ',', '.') ?></td>
                    <td>R$<?= number_format($lucroUnitario, 2, ',', '.') ?></td>
                    <td><?= number_format($porcentagemLucro, 2, ',', '.') ?>%</td>
                    <td>R$<?= number_format($totalCusto, 2, ',', '.') ?></td>
                    <td>R$<?= number_format($totalVenda, 2, ',', '.') ?></td>
                    <td>R$<?= number_format($totalLucro, 2, ',', '.') ?></td>
                    <td>R$<?= number_format($dizimo, 2, ',', '.') ?></td>
                    <td>
                        <?php
                        $hoje = date('Y-m-d');
                        $dataValidade = $produto['validade'];
                        $diferenca = (strtotime($dataValidade) - strtotime($hoje)) / (60 * 60 * 24);

                        if ($dataValidade < $hoje): ?>
                            <span style="color: red;">Vencido</span>
                        <?php elseif ((int)$diferenca === 15): ?>
                            <span style="color: orange;">Falta 15 dias</span>
                        <?php elseif ($diferenca < 15 && $diferenca > 0): ?>
                            <span style="color: orange;">Falta <?= (int)$diferenca ?> dias</span>
                        <?php else: ?>
                            <span style="color: green;">Dentro da validade</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="editar.php?id=<?= htmlspecialchars($produto['id']) ?>">Editar</a>
                        <a href="deletar.php?id=<?= htmlspecialchars($produto['id']) ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Deletar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="color: red; text-align: center;">Nenhum produto encontrado no estoque.</p>
    <?php endif; ?>
</body>
</html>
