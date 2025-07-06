<?php
require_once 'php/modelo.php';

if (isset($_GET['id'])) {
    $modelo = new modelo();
    $modelo->deshabilitar($_GET['id']);
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        echo "<div class='alert alert-success'>¡Bien actualizado correctamente!</div>";
    } else {
        echo "<div class='alert alert-danger'>Hubo un error al actualizar el bien.</div>";
    }
    exit;
}
?>