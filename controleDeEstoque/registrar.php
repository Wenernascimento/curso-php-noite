<?php
require_once 'db.php';

try {
    // Dados do usuário a serem cadastrados
    $nome_usuario = 'admin';
    $senha = '12345';
    $tipo_usuario = 'admin'; // Tipo de usuário: admin ou usuario

    // Verificar se o usuário já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nome_usuario = :nome");
    $stmt->bindParam(':nome', $nome_usuario);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "Usuário '$nome_usuario' já existe no sistema.";
        exit;
    }

    // Criptografar a senha com custo adequado
    $senha_hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

    // Inserir o novo usuário
    $stmt = $conn->prepare("INSERT INTO usuarios (nome_usuario, senha_hash, tipo) VALUES (:nome, :senha, :tipo)");
    $stmt->bindParam(':nome', $nome_usuario);
    $stmt->bindParam(':senha', $senha_hash);
    $stmt->bindParam(':tipo', $tipo_usuario);
    $stmt->execute();

    echo "Usuário '$nome_usuario' cadastrado com sucesso! <br>";
    echo "<a href='login.php'>Ir para a página de login</a>";
    
} catch (PDOException $e) {
    echo "Erro ao cadastrar usuário: " . $e->getMessage();
}

?>
