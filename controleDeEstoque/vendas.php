<?php
// Conexão com o banco de dados
$pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Consulta todas as vendas
$stmt = $pdo->query("SELECT v.*, p.nome AS produto_nome FROM vendas v JOIN produtos p ON v.id_produto = p.id ORDER BY v.data_venda DESC");
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Vendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #6DD5FA, #2980B9);
            background-size: 300% 300%;
            animation: gradientBG 8s ease infinite;
            color: #444;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        h1 {
            text-align: center;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
        }

        table {
            width: 90%;
            max-width: 900px;
            border-collapse: collapse;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #2980B9;
            color: #fff;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e1f5fe;
        }

        td {
            color: #333;
        }

        td:first-child, th:first-child {
            text-align: center;
        }

        /* Estilo para centralizar a data e hora */
        .data-col {
            text-align: center;
        }
    </style>
</head>
<body>
    <div>
        <h1>Histórico de Vendas</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Total</th>
                    <th class="data-col">Data</th> <!-- Adicionando a classe para centralizar -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vendas as $venda): ?>
                    <tr>
                        <td><?= htmlspecialchars($venda['id']) ?></td>
                        <td><?= htmlspecialchars($venda['produto_nome']) ?></td>
                        <td><?= htmlspecialchars($venda['quantidade']) ?></td>
                        <td>R$ <?= number_format($venda['total'], 2, ',', '.') ?></td>
                        <td class="data-col"><?= htmlspecialchars($venda['data_venda']) ?></td> <!-- Centralizando a data -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
