<?php

require_once 'php/bien.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bien = new bien();
    $exito = $bien->crear($_POST['serie'], $_POST['nombre'], $_POST['descripcion'], $_POST['categoria'] ?? 1, $_POST['add'], $_POST['marca'], $_POST['modelo'], $_POST['estado'], $_POST['imagen'] ?? '', $_POST['acta'] ?? '');
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        $_SESSION['mensaje'] = "<div class='alert alert-success'>¡Bien registrado correctamente!</div>";
    } else {
        $_SESSION['mensaje'] = "<div class='alert alert-danger'>Hubo un error al registrar el bien.</div>";
    }
    header("Location: index.php?vista=form_registrar_bien");
    exit;
}
?>
