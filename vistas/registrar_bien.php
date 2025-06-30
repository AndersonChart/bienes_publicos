<?php

require_once 'php/bien.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bien = new bien();
    $bien->crear($_POST['serie'], $_POST['nombre'], $_POST['descripcion'], $_POST['categoria'] ?? 1, $_POST['add'], $_POST['marca'], $_POST['modelo'], $_POST['estado'], $_POST['imagen'] ?? '', $_POST['acta'] ?? '');
    // Redireccionar después de registrar
    header('Location: index.php?vista=listar_bien');
    exit;
}
?>