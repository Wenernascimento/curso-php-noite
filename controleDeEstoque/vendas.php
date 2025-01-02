<?php

// Conexão com o banco de dados
try {
    $pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Função para excluir uma venda
if (isset($_GET['delete'])) {
    $idVenda = $_GET['delete'];

    // Consulta para deletar a venda
    $stmtDelete = $pdo->prepare("DELETE FROM vendas WHERE id = :id");
    $stmtDelete->bindParam(':id', $idVenda, PDO::PARAM_INT);
    $stmtDelete->execute();
    
    // Redireciona para a página sem parâmetros de exclusão
    header("Location: vendas.php");
    exit();
}

// Inicialização de variáveis de filtro
$mes = '';
$ano = '';
$produtoId = '';
$precoMin = '';
$precoMax = '';
$dataDia = '';
$formaPagamento = '';

// Captura os dados do formulário de filtro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mes = $_POST['mes'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $produtoId = $_POST['produto'] ?? '';
    $precoMin = $_POST['preco_min'] ?? '';
    $precoMax = $_POST['preco_max'] ?? '';
    $dataDia = $_POST['data_dia'] ?? '';
    $formaPagamento = $_POST['forma_pagamento'] ?? '';

    // Consulta para vendas filtradas
    $query = "SELECT v.*, p.nome AS produto_nome, v.forma_pagamento FROM vendas v JOIN produtos p ON v.id_produto = p.id";
    $whereClauses = [];
    $params = [];

    if (!empty($mes) && !empty($ano)) {
        $whereClauses[] = "MONTH(v.data_venda) = :mes AND YEAR(v.data_venda) = :ano";
        $params['mes'] = $mes;
        $params['ano'] = $ano;
    }

    if (!empty($dataDia)) {
        $whereClauses[] = "DATE(v.data_venda) = :data_dia";
        $params['data_dia'] = $dataDia;
    }

    if (!empty($produtoId)) {
        $whereClauses[] = "v.id_produto = :produto";
        $params['produto'] = $produtoId;
    }

    if (!empty($precoMin)) {
        $whereClauses[] = "v.total >= :preco_min";
        $params['preco_min'] = $precoMin;
    }

    if (!empty($precoMax)) {
        $whereClauses[] = "v.total <= :preco_max";
        $params['preco_max'] = $precoMax;
    }

    if (!empty($formaPagamento)) {
        $whereClauses[] = "v.forma_pagamento = :forma_pagamento";
        $params['forma_pagamento'] = $formaPagamento;
    }

    if (count($whereClauses) > 0) {
        $query .= " WHERE " . implode(' AND ', $whereClauses);
    }

    $query .= " ORDER BY v.data_venda DESC";
    $stmtFiltradas = $pdo->prepare($query);
    $stmtFiltradas->execute($params);
    $vendasFiltradas = $stmtFiltradas->fetchAll(PDO::FETCH_ASSOC);

    $stmtTotalFiltradas = $pdo->prepare("SELECT SUM(total) FROM vendas v JOIN produtos p ON v.id_produto = p.id WHERE " . implode(' AND ', $whereClauses));
    $stmtTotalFiltradas->execute($params);
    $totalVendasFiltradas = $stmtTotalFiltradas->fetchColumn();
} else {
    $stmt = $pdo->query("SELECT v.*, p.nome AS produto_nome, v.forma_pagamento FROM vendas v JOIN produtos p ON v.id_produto = p.id ORDER BY v.data_venda DESC");
    $vendasFiltradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmtDia = $pdo->prepare("SELECT COUNT(*) FROM vendas WHERE DATE(data_venda) = CURDATE()");
$stmtDia->execute();
$vendasNoDia = $stmtDia->fetchColumn();

$stmtTotalDia = $pdo->prepare("SELECT SUM(total) FROM vendas WHERE DATE(data_venda) = CURDATE()");
$stmtTotalDia->execute();
$totalVendasDia = $stmtTotalDia->fetchColumn();

$stmtTotalGeral = $pdo->prepare("SELECT SUM(total) FROM vendas");
$stmtTotalGeral->execute();
$totalGeralVendas = $stmtTotalGeral->fetchColumn();

$anoAtual = date('Y');
$stmtVendasMes = $pdo->prepare("
    SELECT MONTH(data_venda) AS mes, SUM(total) AS total
    FROM vendas
    WHERE YEAR(data_venda) = :ano
    GROUP BY MONTH(data_venda)
    ORDER BY mes
");
$stmtVendasMes->execute(['ano' => $anoAtual]);
$vendasPorMes = $stmtVendasMes->fetchAll(PDO::FETCH_ASSOC);

$meses = [];
$totais = [];
foreach ($vendasPorMes as $venda) {
    $meses[] = DateTime::createFromFormat('!m', $venda['mes'])->format('F');
    $totais[] = $venda['total'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Vendas</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        header {
            width: 100%;
            background: #4CAF50;
            color: white;
            padding: 15px 0;
            text-align: center;
            font-size: 24px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        .filter-form select, .filter-form input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 200px;
        }

        .filter-form button, .voltar-btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .filter-form button:hover, .voltar-btn:hover {
            background-color: #45a049;
        }

        .filter-form .voltar-btn {
            background-color: #2196F3;
        }

        .filter-form .voltar-btn:hover {
            background-color: #1976D2;
        }

        .filter-form .button-group {
            display: flex;
            gap: 10px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }

        .excluir-btn {
            padding: 6px 12px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .excluir-btn:hover {
            background-color: #d32f2f;
        }

        .total-vendas {
            font-weight: bold;
            font-size: 18px;
            margin-top: 20px;
            text-align: center;
        }

        #salesChart {
            max-width: 800px;
            margin: 40px auto;
        }
    </style>
</head>
<body>

<header>
    Histórico de Vendas
</header>

<div class="container">
    <div class="total-vendas">
        <p>Total de vendas hoje: R$ <?= number_format($totalVendasDia, 2, ',', '.') ?></p>
        <?php if (!empty($totalVendasFiltradas)): ?>
            <p>Total das vendas filtradas: R$ <?= number_format($totalVendasFiltradas, 2, ',', '.') ?></p>
        <?php endif; ?>
    </div>

    <h2>Filtros de Pesquisa</h2>
    <div class="filter-form">
        <form method="POST">
            <div>
                <label for="mes">Mês:</label>
                <select name="mes" id="mes">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $mes ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div>
                <label for="ano">Ano:</label>
                <select name="ano" id="ano">
                    <?php for ($a = date('Y'); $a >= 2000; $a--): ?>
                        <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div>
                <label for="data_dia">Data:</label>
                <input type="date" name="data_dia" id="data_dia" value="<?= htmlspecialchars($dataDia) ?>">
            </div>

            <div>
                <label for="forma_pagamento">Forma de Pagamento:</label>
                <select name="forma_pagamento" id="forma_pagamento">
                    <option value="">Todos</option>
                    <option value="credito" <?= $formaPagamento == 'credito' ? 'selected' : '' ?>>Crédito</option>
                    <option value="dinheiro" <?= $formaPagamento == 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                    <option value="pix" <?= $formaPagamento == 'pix' ? 'selected' : '' ?>>PIX</option>
                    <option value="debito" <?= $formaPagamento == 'debito' ? 'selected' : '' ?>>Débito</option>
                </select>
            </div>

            <div class="button-group">
                <button type="submit">Filtrar</button>
                <a href="index.php" class="voltar-btn">Voltar para o Índice</a>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Total</th>
                <th>Data</th>
                <th>Forma de Pagamento</th>
                <th>Excluir</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($vendasFiltradas)): ?>
                <?php foreach ($vendasFiltradas as $venda): ?>
                    <tr>
                        <td><?= htmlspecialchars($venda['id']) ?></td>
                        <td><?= htmlspecialchars($venda['produto_nome']) ?></td>
                        <td><?= htmlspecialchars($venda['quantidade']) ?></td>
                        <td>R$ <?= number_format($venda['total'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($venda['data_venda']) ?></td>
                        <td><?= htmlspecialchars($venda['forma_pagamento']) ?></td>
                        <td><a href="?delete=<?= $venda['id'] ?>" class="excluir-btn" onclick="return confirm('Tem certeza que deseja excluir esta venda?')">Excluir</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nenhuma venda encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<canvas id="salesChart"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const meses = <?= json_encode($meses) ?>;
    const totais = <?= json_encode($totais) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Total de Vendas (R$)',
                data: totais,
                backgroundColor: 'rgba(66, 133, 244, 0.6)',
                borderColor: 'rgb(66, 133, 244)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'x',
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
