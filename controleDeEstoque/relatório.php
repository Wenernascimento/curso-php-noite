<?php
// Conexão com o banco de dados
try {
    $pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Consulta todos os gastos
$stmt = $pdo->query("SELECT * FROM gastos");
$gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para formatar a data
function formatarData($data) {
    // Verifica se a data é válida utilizando DateTime::createFromFormat()
    $date = DateTime::createFromFormat('Y-m-d', $data); // Formato esperado do banco: 'YYYY-MM-DD'
    
    // Verifica se a conversão foi bem-sucedida
    if (!$date) {
        return 'Data inválida';  // Se a data não for válida
    }

    // Formata a data para d/m/Y
    return $date->format('d/m/Y');
}

// Função para formatar valores monetários
function formatarValor($valor) {
    // Verifica se o valor é um número
    if (is_numeric($valor)) {
        return "R$ " . number_format($valor, 2, ',', '.');
    } else {
        return "Valor inválido";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Gastos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h1 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        a {
            text-decoration: none;
            color: #007BFF;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Relatório de Gastos</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gastos as $gasto): ?>
                <tr>
                    <td><?php echo htmlspecialchars($gasto['id']); ?></td>
                    <td><?php echo htmlspecialchars($gasto['descricao']); ?></td>
                    <td><?php echo formatarValor($gasto['valor']); ?></td>
                    <td>
                        <?php 
                        // Usando a função para formatar a data
                        echo formatarData($gasto['data_gasto']);
                        ?>
                    </td>
                    <td>
                        <!-- Link para editar -->
                        <a href="editar_gasto.php?id=<?php echo $gasto['id']; ?>">Editar</a> | 
                        <!-- Link para deletar -->
                        <a href="deletar_gasto.php?id=<?php echo $gasto['id']; ?>" onclick="return confirm('Tem certeza que deseja deletar este gasto?')">Deletar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br><a href="gasto.php">Registrar Novo Gasto</a>
</body>
</html>
