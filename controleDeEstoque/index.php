<?php
// Inicia sessão
session_start();

// Inclui o arquivo de configuração do banco de dados
require_once 'db.php';

// Função para obter todos os produtos do banco de dados com filtro
function obterProdutos($conn, $filtro = '') {
    try {
        // Consulta SQL com filtro, caso haja
        $sql = "SELECT * FROM produtos WHERE nome LIKE :filtro OR id LIKE :filtro OR validade LIKE :filtro";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['filtro' => "%" . $filtro . "%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna todos os produtos como um array associativo
    } catch (PDOException $e) {
        // Em caso de erro no banco de dados, loga a exceção e retorna um array vazio
        error_log("Erro ao obter produtos: " . $e->getMessage());
        return [];
    }
}

// Verifica se existe um filtro de pesquisa
$filtroPesquisa = isset($_GET['search']) ? $_GET['search'] : '';

// Obtém todos os produtos com o filtro
$produtos = obterProdutos($conn, $filtroPesquisa);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Estoque</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #2C3E50; /* Fundo escuro (navy) */
            color: #ECF0F1; /* Texto em cinza claro */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
            overflow-x: hidden;
        }

        h1 {
            color: #F39C12; /* Cor dourada para o título */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 10px;
        }

        .menu {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
            position: sticky;
            top: 0;
            background-color: rgba(46, 204, 113, 0.8); /* Fundo verde */
            padding: 10px;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .menu a {
            text-decoration: none;
            color: white;
            background-color: #27AE60; /* Fundo verde */
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s;
            font-size: 18px;
        }

        .menu a:hover {
            background-color: #2ECC71; /* Verde mais claro no hover */
            transform: scale(1.05);
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-container input {
            padding: 10px;
            width: 300px;
            border-radius: 5px;
            border: 1px solid #27AE60;
            font-size: 16px;
        }

        .table-container {
            width: 100%;
            max-width: 1200px;
            overflow-y: auto;
            height: 60vh;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            background-color: #34495E; /* Cor de fundo da tabela */
        }

        th, td {
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #E74C3C; /* Fundo vermelho para cabeçalho */
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.1);
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
            color: #FF5733; /* Cor vermelha */
        }

        .status.orange {
            color: #F39C12; /* Cor dourada */
        }

        .status.green {
            color: #27AE60; /* Verde para dentro da validade */
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .actions a {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .actions a:first-child {
            background-color: #2980B9; /* Azul para editar */
            color: white;
        }

        .actions a:first-child:hover {
            background-color: #3498DB; /* Azul mais claro no hover */
        }

        .actions a:last-child {
            background-color: #FF6347; /* Cor de fundo para deletar */
            color: white;
        }

        .actions a:last-child:hover {
            background-color: #FF4500; /* Cor mais forte para deletar no hover */
        }

        td.id-col {
            background-color: #2980B9; /* Azul para a coluna ID */
            color: white; /* Texto branco */
            font-weight: bold;
        }

    </style>
</head>
<body>
    <h1>Controle de Estoque</h1>

    <div class="menu">
        <a href="venda.php">Venda</a>
        <a href="vendas.php">Histórico de Vendas</a>
        <a href="cadastrar.php">Cadastrar Produto</a>
    </div>

    <!-- Formulário de pesquisa -->
    <div class="search-container">
        <form method="get" action="">
            <input type="text" name="search" placeholder="Buscar produto por ID, Nome ou Validade" value="<?= htmlspecialchars($filtroPesquisa) ?>" />
        </form>
    </div>

    <div class="table-container">
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
                            $preco = $produto['preco'];
                            $quantidade = $produto['quantidade'];
                            $valorVenda = $preco * 1.5;
                            $lucroUnitario = $valorVenda - $preco;
                            $porcentagemLucro = ($lucroUnitario / $preco) * 100;
                            $totalCusto = $preco * $quantidade;
                            $totalVenda = $valorVenda * $quantidade;
                            $totalLucro = $totalVenda - $totalCusto;
                            $dizimo = $totalLucro * 0.1;
                        ?>

                        <tr>
                            <td class="id-col"><?= htmlspecialchars($produto['id']) ?></td> <!-- Azul para a coluna ID -->
                            <td><?= htmlspecialchars($produto['nome']) ?></td>
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
                                        <span class="status red">Vencido</span>
                                    <?php elseif ($diferenca <= 20 && $diferenca > 0): ?>
                                        <span class="status orange">Falta <?= (int)$diferenca ?> dias</span>
                                    <?php else: ?>
                                        <span class="status green">Dentro da validade</span>
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
            <p style="color: red;">Nenhum produto encontrado no estoque.</p>
        <?php endif; ?>
    </div>
</body>
</html>
