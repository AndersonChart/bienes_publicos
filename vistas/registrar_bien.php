<?php

require_once 'php/bien.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bien = new bien();
    $bien->crear($_POST['serie'], $_POST['nombre'], $_POST['descripcion'], $_POST['categoria'] ?? 1, $_POST['add'], $_POST['marca'], $_POST['modelo'], $_POST['estado'], $_POST['imagen'] ?? '', $_POST['acta'] ?? '');
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        echo "<div class='alert alert-success'>¡Bien actualizado correctamente!</div>";
    } else {
        echo "<div class='alert alert-danger'>Hubo un error al actualizar el bien.</div>";
    }
    exit;
}
?>