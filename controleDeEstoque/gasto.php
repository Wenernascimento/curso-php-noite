<?php
// Conexão com o banco de dados
try {
    $pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Definir a data atual por padrão (no formato d/m/Y)
$data_atual = date('Y-m-d');  // Mudando para o formato do banco (Y-m-d)

// Variáveis para mensagens de sucesso ou erro
$mensagem = '';  // Defina a variável para evitar o erro "undefined"
$erro = false;

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $categoria = $_POST['categoria'];
    $data_gasto = $_POST['data_gasto'];

    // Validação: Verifica se os campos estão vazios
    if (empty($descricao) || empty($valor) || empty($categoria) || empty($data_gasto)) {
        $mensagem = 'Todos os campos são obrigatórios!';
        $erro = true;
    }

    // Valida o valor como número positivo
    if (!is_numeric($valor) || $valor <= 0) {
        $mensagem = 'O valor deve ser um número positivo!';
        $erro = true;
    }

    // Valida a data (formato Y-m-d)
    $date = DateTime::createFromFormat('Y-m-d', $data_gasto);
    if (!$date || $date->format('Y-m-d') !== $data_gasto) {
        $mensagem = 'Data inválida. Por favor, insira uma data válida.';
        $erro = true;
    }

    // Se não houve erro, insere os dados no banco
    if (!$erro) {
        try {
            // Insere os dados no banco de dados
            $sql = "INSERT INTO gastos (descricao, valor, categoria, data_gasto) VALUES (:descricao, :valor, :categoria, :data_gasto)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':descricao' => $descricao,
                ':valor' => $valor,
                ':categoria' => $categoria,
                ':data_gasto' => $data_gasto
            ]);

            // Mensagem de sucesso
            $mensagem = 'Gasto registrado com sucesso!';
        } catch (Exception $e) {
            // Se ocorrer um erro ao inserir no banco
            $mensagem = 'Erro ao registrar o gasto: ' . $e->getMessage();
            $erro = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Novo Gasto</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #eeeeee;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        label {
            font-size: 16px;
            color: #333;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"], input[type="number"], select, input[type="date"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            font-size: 16px;
            background-color: #fafafa;
        }

        input[type="text"]:focus, input[type="number"]:focus, select:focus, input[type="date"]:focus {
            border-color: #3f51b5;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #3f51b5;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2c387e;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message.error {
            color: #f44336;
        }

        .message.success {
            color: #4caf50;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h1>Registrar Novo Gasto</h1>
    <div class="message <?php echo $erro ? 'error' : 'success'; ?>">
        <?php echo $mensagem; ?>
    </div>

    <form action="gasto.php" method="POST">
        <label for="descricao">Descrição</label>
        <input type="text" name="descricao" id="descricao" value="<?php echo isset($descricao) ? $descricao : ''; ?>" required>

        <label for="valor">Valor</label>
        <input type="number" name="valor" id="valor" step="0.01" value="<?php echo isset($valor) ? $valor : ''; ?>" required>

        <label for="categoria">Categoria</label>
        <select name="categoria" id="categoria" required>
            <option value="">Selecione a categoria</option>
            <option value="agua" <?php echo isset($categoria) && $categoria == 'agua' ? 'selected' : ''; ?>>Água</option>
            <option value="fornecedor" <?php echo isset($categoria) && $categoria == 'fornecedor' ? 'selected' : ''; ?>>Fornecedores</option>
            <option value="servicos" <?php echo isset($categoria) && $categoria == 'servicos' ? 'selected' : ''; ?>>Serviços</option>
        </select>

        <label for="data_gasto">Data</label>
        <input type="date" name="data_gasto" id="data_gasto" value="<?php echo isset($data_gasto) ? $data_gasto : $data_atual; ?>" required>

        <button type="submit">Registrar</button>
    </form>
</div>

</body>
</html>
