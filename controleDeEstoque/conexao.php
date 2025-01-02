<?php
// conexao.php
$host = 'localhost';  // Ou o seu servidor de banco de dados
$user = 'root';       // Usuário do banco de dados (normalmente 'root' no XAMPP)
$password = '';       // Senha do banco de dados (deixe vazio se for o padrão do XAMPP)
$database = 'estoque'; // Nome do banco de dados

// Criar conexão
$conn = mysqli_connect($host, $user, $password, $database);

// Verificar conexão
if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}
?>
