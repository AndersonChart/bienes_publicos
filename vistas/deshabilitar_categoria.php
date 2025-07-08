<?php
require_once 'php/categoria.php';

if (isset($_GET['id'])) {
    $categoria = new categoria();
    $exito = $categoria->deshabilitar($_GET['id']);
    // Puedes comprobar si la actualización fue exitosa o no
    if($exito){
        $_SESSION['mensaje'] = "<div class='alert alert-success'>¡Categoría eliminada correctamente!</div>";
    } else {
        $_SESSION['mensaje'] = "<div class='alert alert-danger'>Hubo un error al eliminar la categoría.</div>";
    }
    
    header("Location: index.php?vista=listar_categoria");
    exit;
}
?>