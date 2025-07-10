<?php

require_once 'php/bien.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST["id"]) ? $_POST["id"] : '';
    $bien = new bien();
    $exito = $bien->actualizar($_POST['serie'], $_POST['nombre'], $_POST['descripcion'], $_POST['categoria'], $_POST['add'], $_POST['marca'], $_POST['modelo'], $_POST['estado'], $_POST['imagen'] ?? '', $_POST['acta'] ?? '', $_POST['id']);
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        $_SESSION['mensaje'] = "<div class='alert alert-success'>¡Bien actualizado correctamente!</div>";
    } else {
        $_SESSION['mensaje'] = "<div class='alert alert-danger'>Hubo un error al actualizar el bien.</div>";
    }
    header("Location: index.php?vista=form_actualizar_bien&id=". $id);
    exit;
}
?>