<?php
require 'db.php';

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM produtos WHERE id = :id");
$stmt->execute(['id' => $id]);

header('Location: index.php');
?>
