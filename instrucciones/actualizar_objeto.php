<?php
// Llamar a las funciones del 'objeto' para actualizar
require_once 'objeto.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objeto = new objeto();
    $objeto->actualizar($_POST['id'], $_POST['campo1'], $_POST['campo2']);
    header('Location: listar_objeto.php');
    exit;
}
?>