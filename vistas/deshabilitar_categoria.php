<?php
require_once 'php/categoria.php';

if (isset($_GET['id'])) {
    $categoria = new categoria();
    $categoria->deshabilitar($_GET['id']);
    header('Location: index.php?vista=listar_categoria');
    exit;
}
?>