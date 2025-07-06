<?php

require_once 'marca.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = new marca();
    $marca->actualizar($_POST['nombre'], $_POST['id']);
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        echo "<div class='alert alert-success'>¡Bien actualizado correctamente!</div>";
    } else {
        echo "<div class='alert alert-danger'>Hubo un error al actualizar el bien.</div>";
    }
    exit;
}
?>