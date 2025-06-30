<?php
// Llamar al archivo del 'objeto', recuerda que debes colocarlo segun a tu modulo
require_once 'objeto.php';

// Validación básica: ayuda a verificar datos, si son datos sensibles se deben verificar dato por dato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objeto = new objeto();
    $objeto->crear($_POST['campo1'], $_POST['campo2']);
    // Redireccionar después de registrar
    header('Location: listar_objeto.php');
    exit;
}
?>