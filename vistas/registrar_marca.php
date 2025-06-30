<?php

require_once 'php/categoria.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = new categoria();
    $categoria->crear($_POST['nombre'], $_POST['descripcion'] ?? '');
    // Redireccionar después de registrar
    header('Location: index.php?vista=listar_categoria');
    exit;
}
?>