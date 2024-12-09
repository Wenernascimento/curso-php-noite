<?php
// Conexão com o banco de dados
$pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    // Executa a consulta filtrada
    $stmtFiltradas = $pdo->prepare($query);
    $stmtFiltradas->execute($params);
    $vendasFiltradas = $stmtFiltradas->fetchAll(PDO::FETCH_ASSOC);

    // Soma das vendas filtradas
    $stmtTotalFiltradas = $pdo->prepare("SELECT SUM(total) FROM vendas v JOIN produtos p ON v.id_produto = p.id WHERE " . implode(' AND ', $whereClauses));
    $stmtTotalFiltradas->execute($params);
    $totalVendasFiltradas = $stmtTotalFiltradas->fetchColumn();
} else {
    // Consulta todas as vendas
    $stmt = $pdo->query("SELECT v.*, p.nome AS produto_nome, v.forma_pagamento FROM vendas v JOIN produtos p ON v.id_produto = p.id ORDER BY v.data_venda DESC");
    $vendasFiltradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Consultas para contagem e totais de vendas no dia e geral
$stmtDia = $pdo->prepare("SELECT COUNT(*) FROM vendas WHERE DATE(data_venda) = CURDATE()");
$stmtDia->execute();
$vendasNoDia = $stmtDia->fetchColumn();

$stmtTotalDia = $pdo->prepare("SELECT SUM(total) FROM vendas WHERE DATE(data_venda) = CURDATE()");
$stmtTotalDia->execute();
$totalVendasDia = $stmtTotalDia->fetchColumn();

$stmtTotalGeral = $pdo->prepare("SELECT SUM(total) FROM vendas");
$stmtTotalGeral->execute();
$totalGeralVendas = $stmtTotalGeral->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Vendas</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            background-attachment: fixed;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        h1 {
            text-align: center;
            color: #fff;
            margin-top: 20px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            background: #ffffffcc;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
            margin: 20px 0;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between;
            align-items: center;
        }

        .filter-form label {
            font-weight: bold;
            color: #444;
        }

        .filter-form select,
        .filter-form input,
        .filter-form button {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
            width: calc(25% - 10px);
            box-sizing: border-box;
        }

        .filter-form button {
            background-color: #0078ff;
            color: white;
            border: none;
            transition: 0.3s ease;
            cursor: pointer;
        }

        .filter-form button:hover {
            background-color: #005bb5;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #0078ff;
            color: white;
            font-weight: bold;
        }

        table tr:hover {
            background-color: #f0f8ff;
        }

        .total-vendas {
            font-size: 18px;
            margin-top: 20px;
            font-weight: bold;
            color: #555;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Histórico de Vendas</h1>
    <div class="container">
        <div class="filter-form">
            <form method="POST">
                <label for="mes">Mês:</label>
                <select name="mes" id="mes">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $mes ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>

                <label for="ano">Ano:</label>
                <select name="ano" id="ano">
                    <?php for ($a = date('Y'); $a >= 2000; $a--): ?>
                        <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>

                <label for="data_dia">Data do Dia:</label>
                <input type="date" name="data_dia" id="data_dia" value="<?= htmlspecialchars($dataDia) ?>">

                <label for="forma_pagamento">Forma de Pagamento:</label>
                <select name="forma_pagamento" id="forma_pagamento">
                    <option value="">Todos</option>
                    <option value="credito" <?= $formaPagamento == 'credito' ? 'selected' : '' ?>>Crédito</option>
                    <option value="dinheiro" <?= $formaPagamento == 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                    <option value="pix" <?= $formaPagamento == 'pix' ? 'selected' : '' ?>>PIX</option>
                    <option value="debito" <?= $formaPagamento == 'debito' ? 'selected' : '' ?>>Débito</option>
                </select>

                <button type="submit">Filtrar</button>
                <a href="index.php" style="text-decoration:none;"><button type="button" style="background-color:#28a745;">Voltar ao Estoque</button></a>
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
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Nenhuma venda encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="total-vendas">
            <p>Total de vendas hoje: R$ <?= number_format($totalVendasDia, 2, ',', '.') ?></p>
            <p>Total geral de vendas: R$ <?= number_format($totalGeralVendas, 2, ',', '.') ?></p>
            <?php if (!empty($totalVendasFiltradas)): ?>
                <p>Total das vendas filtradas: R$ <?= number_format($totalVendasFiltradas, 2, ',', '.') ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
