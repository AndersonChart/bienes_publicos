<?php

require_once 'php/modelo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = new modelo();
    $modelo->crear($_POST['nombre'], $_POST['marca']);
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        echo "<div class='alert alert-success'>¡Bien actualizado correctamente!</div>";
    } else {
        echo "<div class='alert alert-danger'>Hubo un error al actualizar el bien.</div>";
    }
    exit;
}
?>