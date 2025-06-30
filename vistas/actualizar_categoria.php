<?php

require_once 'php/categoria.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = new categoria();
    $categoria->actualizar($_POST['nombre'], $_POST['descripcion'] ?? '', $_POST['id']);
    header('Location: index.php?vista=listar_categoria');
    exit;
}
?>