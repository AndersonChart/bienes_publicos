<?php
// Llamar al archivo del 'objeto' y llamar la funcion para leer los datos por el id seleccionado
require_once 'objeto.php';
$objeto = new objeto();
$registro = $objeto->leer_por_id($_GET['id']);
?>
<form action="actualizar_objeto.php" method="POST">
<!-- Este primer input es esencial para saber de que registro se trata, pero no es visible ante el usuario -->
    <input type="hidden" name="id" value="<?= $registro['id'] ?>">
    <input type="text" name="campo1" value="<?= $registro['campo1'] ?>" required>
    <input type="text" name="campo2" value="<?= $registro['campo2'] ?>" required>
    <button type="submit">Actualizar</button>
</form>