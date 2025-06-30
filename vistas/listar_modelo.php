<?php

require_once 'php/modelo.php';
$modelo = new modelo();
$registros = $modelo->leer_todos();

?>
<button><a href="index.php?vista=inicio">Volver</a></button>
<h1>Lista de modelos</h1>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Modelo</th>
        <th>Marca</th>
        <!-- El siguiente es para los botones 'actualizar' y 'eliminar' -->
        <th>Acciones</th>
    </tr>

    <?php if (!empty($registros)){ 
    foreach ($registros as $row){ ?>
    <tr>
        <td><?= $row['modelo_id'] ?></td>
        <td><?= $row['modelo_nombre'] ?></td>
        <td><?= $row['marca_nombre'] ?></td>
        <!-- Aqui se coloca los botones para 'actualizar' y 'eliminar' por el id -->
        <th>
            <!-- Debido que el valor por defecto 'Ninguno' tiene id=1 no se puede hacer ningún tipo de acción que modifique su funcionamiento -->
            <?php if($row['modelo_id']!=1){ ?>
            <button><a href="index.php?vista=form_actualizar_modelo&id=<?= $row['modelo_id']  ?>">Actualizar</a></button>
            <button><a href="index.php?vista=deshabilitar_modelo&id=<?= $row['modelo_id'] ?>" onclick="return confirm('¡¡AVISO!! Eliminar este modelo hará que todos los registros de este modelo tengan el valor [Desconocido]')">Eliminar</a></button>
            <?php } ?>
            <button><a href="index.php?vista=listar_por_modelo.php?id=<?= $row['modelo_id'] ?>">Ver Bienes</a></button>
        </th>
    </tr>
    <?php }
    }else{  ?>

    <th colspan="4">No hay ningún registro</th>

    <?php
    }  
    ?>
</table>
<!-- Aqui el enlace para seguir creando registros -->
<button><a href="index.php?vista=form_registrar_modelo">Nuevo Registro</a></button>