<?php

require_once 'marca.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = new marca();
    $marca->actualizar($_POST['nombre'], $_POST['id']);
    header('Location: index.php?vista=listar_marca');
    exit;
}
?>