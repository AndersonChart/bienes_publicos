<?php
require_once 'php/modelo.php';

if (isset($_GET['id'])) {
    $modelo = new modelo();
    $modelo->deshabilitar($_GET['id']);
    header('Location: index.php?vista=listar_modelo');
    exit;
}
?>