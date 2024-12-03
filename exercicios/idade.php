<?php
// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inicializa um array para armazenar as idades
    $idades = array();

    // Recebe as idades do formulário
    for ($i = 0; $i < 5; $i++) {
        $idade = $_POST['idade' . $i];
        $idades[] = $idade;
    }

    // Exibe a classificação das idades
    echo "<h2>Classificação das idades inseridas:</h2>";
    foreach ($idades as $i => $idade) {
        // Classificação baseada na faixa etária
        if ($idade >= 0 && $idade <= 12) {
            $classe = "Criança";
        } elseif ($idade >= 13 && $idade <= 17) {
            $classe = "Adolescente";
        } elseif ($idade >= 18 && $idade <= 59) {
            $classe = "Adulto";
        } elseif ($idade >= 60) {
            $classe = "Idoso";
        } else {
            $classe = "Idade inválida"; // Caso a idade seja negativa ou errada
        }

        // Exibe a idade e a classificação
        echo "Pessoa " . ($i + 1) . " - Idade: $idade - Classificação: $classe<br>";
    }
}
?>

<!-- Formulário HTML para inserir as idades -->
<form method="POST" action="">
    <h2>Insira a idade das 5 pessoas:</h2>
    <?php
    // Cria 5 campos de entrada para as idades
    for ($i = 0; $i < 5; $i++) {
        echo "Idade da pessoa " . ($i + 1) . ": <input type='number' name='idade$i' required><br><br>";
    }
    ?>
    <input type="submit" value="Enviar">
</form>
