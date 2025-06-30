<?php

require_once 'php/modelo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = new modelo();
    $modelo->actualizar($_POST['nombre'], $_POST['marca'], $_POST['id']);
    header('Location: index.php?vista=listar_modelo');
    exit;
}
?>