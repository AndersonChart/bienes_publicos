<?php
require_once 'php/bien.php';

if (isset($_GET['id'])) {
    $id = isset($_POST["id"]) ? $_POST["id"] : '';
    $bien = new bien();
    $exito = $bien->desincorporar($_GET['id']);
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        $_SESSION['mensaje'] = "<div class='alert alert-success'>¡Bien desincorporado correctamente!</div>";
    } else {
        $_SESSION['mensaje'] = "<div class='alert alert-danger'>Hubo un error al desincorporado el bien.</div>";
    }
    header("Location: index.php?vista=listar_bien");
    exit;
}
?>