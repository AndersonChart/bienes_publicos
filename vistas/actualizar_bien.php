<?php

require_once 'bien.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bien = new bien();
    $bien->actualizar($_POST['serie'], $_POST['nombre'], $_POST['descripcion'], $_POST['categoria'], $_POST['add'], $_POST['marca'], $_POST['modelo'], $_POST['estado'], $_POST['imagen'] ?? '', $_POST['acta'] ?? '', $_POST['id']);
    header('Location: index.php?vista=listar_bien');
    exit;
}
?>