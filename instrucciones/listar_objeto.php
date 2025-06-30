<?php
// Llamar al archivo del 'objeto' y llamar la funcion para enlistar
require_once 'objeto.php';
$objeto = new objeto();
$registros = $objeto->leer_todos();
?>

<!-- Generar la tabla con los titulos de cada campo respectivo al objeto -->
<table border="1">
    <tr>
        <th>ID</th>
        <th>Campo 1</th>
        <th>Campo 2</th>
        <!-- El siguiente es para colocar los botones 'actualizar' y 'eliminar' -->
        <th>Acciones</th>
    </tr>
    <!-- Crear el bucle foreach para recorrer los datos del 'objeto' -->
    <?php foreach ($registros as $row){ ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['campo1'] ?></td>
        <td><?= $row['campo2'] ?></td>
        <!-- Aqui se coloca los botones para 'actualizar' y 'eliminar' por el id -->
        <td>
            <button><a href="formulario_actualizar.php?id=<?= $row['id'] ?>">Actualizar</a></button>
            <button><a href="eliminar_objeto.php?id=<?= $row['id'] ?>" onclick="return confirm('¿Estás seguro de Eliminar el Registro?')">Eliminar</a></button>
        </td>
    </tr>
    <?php } ?>
</table>
<!-- Aqui el enlace para seguir creando registros -->
<button><a href="formulario_registrar.php">Nuevo Registro</a></button>