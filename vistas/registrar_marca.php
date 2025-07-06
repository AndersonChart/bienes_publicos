<?php

require_once 'php/categoria.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = new categoria();
    $categoria->crear($_POST['nombre'], $_POST['descripcion'] ?? '');
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        echo "<div class='alert alert-success'>¡Bien actualizado correctamente!</div>";
    } else {
        echo "<div class='alert alert-danger'>Hubo un error al actualizar el bien.</div>";
    }
    exit;
}
?>