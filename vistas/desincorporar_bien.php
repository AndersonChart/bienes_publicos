<?php
require_once 'php/bien.php';

if (isset($_GET['id'])) {
    $bien = new bien();
    $bien->desincorporar($_GET['id']);
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        echo "<div class='alert alert-success'>¡Bien actualizado correctamente!</div>";
    } else {
        echo "<div class='alert alert-danger'>Hubo un error al actualizar el bien.</div>";
    }
    exit;
}
?>