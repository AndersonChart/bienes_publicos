<?php
require_once 'objeto.php';

if (isset($_GET['id'])) {
    $objeto = new objeto();
    $objeto->eliminar($_GET['id']);
    header('Location: listar_objeto.php');
    exit;
}
?>