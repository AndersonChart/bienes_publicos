<?php
require_once 'php/marca.php';

if (isset($_GET['id'])) {
    $marca = new marca();
    $marca->deshabilitar($_GET['id']);
    header('Location: index.php?vista=listar_marca');
    exit;
}
?>