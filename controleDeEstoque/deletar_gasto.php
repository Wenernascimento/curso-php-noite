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

    // Deleta o gasto no banco de dados
    $stmt = $pdo->prepare("DELETE FROM gastos WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo "Gasto deletado com sucesso!";
} else {
    echo "ID não fornecido.";
}
?>
