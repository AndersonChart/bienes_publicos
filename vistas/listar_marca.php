<?php

require_once 'php/marca.php';
$marca = new marca();
$registros = $marca->leer_todos();
?>
<button><a href="index.php?vista=inicio">Volver</a></button>
<h1>Lista de Marcas</h1>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <!-- El siguiente es para los botones 'actualizar' y 'eliminar' -->
        <th>Acciones</th>
    </tr>

    <?php if (!empty($registros)){ 
    
    foreach ($registros as $row){ ?>
    <tr>
        <td><?= $row['marca_id'] ?></td>
        <td><?= $row['marca_nombre'] ?></td>
        <td><?= $row['marca_imagen'] ?></td>
        <!-- Aqui se coloca los botones para 'actualizar' y 'eliminar' por el id -->
        <th>
            <!-- Debido que el valor por defecto 'Ninguno' tiene id=1 no se puede hacer ningún tipo de acción que modifique su funcionamiento -->
            <?php if($row['marca_id']!=1){ ?>
            <button><a href="index.php?vista=form_actualizar_marca&id=<?= $row['marca_id'] ?>">Actualizar</a></button>
            <button><a href="index.php?vista=deshabilitar_marca&id=<?= $row['marca_id'] ?>" onclick="return confirm('¡¡AVISO!! Eliminar esta marca hará que todos los registros de esta marca tengan el valor [Desconocido]')">Eliminar</a></button>
            <?php } ?>
            <button><a href="index.php?vista=listar_por_marca.php?id=<?= $row['marca_id'] ?>">Ver Bienes</a></button>
        </th>
    </tr>
    <?php }
    }else{  ?>

    <th colspan="3">No hay ningún registro</th>

    <?php
    }  
    ?>
</table>
<!-- Aqui el enlace para seguir creando registros -->
<button><a href="index.php?vista=form_registrar_marca">Nuevo Registro</a></button>