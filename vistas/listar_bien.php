<?php

require_once 'php/bien.php';
$bien = new bien();
$registros = $bien->leer_todos();
?>
<button><a href="index.php?vista=inicio">Volver</a></button>
<h1>Lista de Bienes</h1>
<table border="1">
    <tr>
        <th>ID</th>
        <th>No. Serie</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Categoria</th>
        <th>Fecha de add.</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Estado</th>
        <th>Imagen</th>
        <th>Acta</th>
        <!-- El siguiente es para los botones 'actualizar' y 'desincorporar' -->
        <th>Acciones</th>
    </tr>

    <?php if (!empty($registros)){
    foreach ($registros as $row){ ?>
    <tr>
        <td><?= $row['bien_id'] ?></td>
        <td><?= $row['bien_serie'] ?></td>
        <td><?= $row['bien_nombre'] ?></td>
        <td><?= $row['bien_descripcion'] ?></td>
        <td><?= $row['categoria_id'] ?></td>
        <td><?= $row['fecha_add'] ?></td>
        <td><?= $row['marca_id'] ?></td>
        <td><?= $row['modelo_id'] ?></td>
        <td><?= $row['estado_id'] ?></td>
        <td><?= $row['bien_imagen'] ?></td>
        <td><?= $row['bien_acta'] ?></td>
        <!-- Aqui se coloca los botones para 'actualizar' y 'desincorporar' por el id -->
        <td>
            <button><a href="index.php?vista=form_actualizar_bien&id=<?= $row['bien_id'] ?>">Actualizar</a></button>
            <button><a href="index.php?vista=desincorporar_bien&id=<?= $row['bien_id'] ?>" onclick="return confirm('¿Estás seguro de Desincorporar este Bien?')">Desincorporar</a></button>
        </td>
    </tr>
    <?php }
    }else{  ?>

    <th colspan="12">No hay ningún registro</th>

    <?php
    }
    ?>
</table>
<!-- Aqui el enlace para seguir creando registros -->
<button><a href="index.php?vista=form_registrar_bien">Nuevo Registro</a></button>