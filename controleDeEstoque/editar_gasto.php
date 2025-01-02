<?php
// Conexão com o banco de dados
try {
    $pdo = new PDO('mysql:host=localhost;dbname=estoque', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Verifica se o ID foi passado na URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Buscar o gasto específico no banco
    $stmt = $pdo->prepare("SELECT * FROM gastos WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $gasto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifica se o gasto existe
    if (!$gasto) {
        echo "Gasto não encontrado!";
        exit;
    }
}

// Atualiza o gasto se o formulário for submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data_gasto = $_POST['data_gasto'];

    // Atualiza o gasto no banco de dados
    $stmt = $pdo->prepare("UPDATE gastos SET descricao = :descricao, valor = :valor, data_gasto = :data_gasto WHERE id = :id");
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':data_gasto', $data_gasto);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo "Gasto atualizado com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Gasto</title>
</head>
<body>
    <h1>Editar Gasto</h1>
    <form action="editar_gasto.php?id=<?php echo $gasto['id']; ?>" method="POST">
        <label for="descricao">Descrição:</label>
        <input type="text" name="descricao" id="descricao" value="<?php echo $gasto['descricao']; ?>" required><br><br>
        
        <label for="valor">Valor (R$):</label>
        <input type="number" name="valor" id="valor" step="0.01" value="<?php echo $gasto['valor']; ?>" required><br><br>
        
        <label for="data_gasto">Data do Gasto:</label>
        <input type="date" name="data_gasto" id="data_gasto" value="<?php echo $gasto['data_gasto']; ?>" required><br><br>

        <button type="submit">Salvar Alterações</button>
    </form>
</body>
</html>
