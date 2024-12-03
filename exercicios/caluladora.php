<?php
// Verifica se o formulário foi enviado
if (isset($_POST['numero'])) {
    // Recebe o número fornecido pelo usuário
    $numero = $_POST['numero'];

    // Verifica se o número fornecido é válido
    if (!is_numeric($numero) || $numero <= 0) {
        echo "Por favor, insira um número inteiro positivo válido.";
    } else {
        // Inicializa a variável para a soma
        $soma = 0;
        $i = 1; // Inicia o contador

        // Laço do-while para percorrer os números de 1 até o número fornecido
        do {
            // Verifica se o número é par
            if ($i % 2 == 0) {
                $soma += $i; // Adiciona o número par à soma
            }
            $i++; // Incrementa o contador
        } while ($i <= $numero); // Continua enquanto $i for menor ou igual ao número fornecido

        // Exibe o resultado da soma dos números pares
        echo "A soma de todos os números pares de 1 até $numero é: $soma<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soma de Números Pares</title>
</head>
<body>
    <h2>Digite um número inteiro positivo:</h2>

    <!-- Formulário para capturar a entrada do usuário -->
    <form method="POST" action="">
        <input type="number" name="numero" min="1" required>
        <button type="submit">Calcular</button>
    </form>
</body>
</html>
