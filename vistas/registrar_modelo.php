<?php

require_once 'php/modelo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = new modelo();
    $modelo->crear($_POST['nombre'], $_POST['marca']);
    // Redireccionar después de registrar
    header('Location: index.php?vista=listar_modelo');
    exit;
}
?>