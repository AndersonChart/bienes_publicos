<?php

require_once 'php/categoria.php';
$categoria = new categoria();
$registros = $categoria->leer_todos();
?>
<button><a href="index.php?vista=inicio">Volver</a></button>
<h1>Lista de Categorias</h1>
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
        <td><?= $row['categoria_id'] ?></td>
        <td><?= $row['categoria_nombre'] ?></td>
        <td><?= $row['categoria_descripcion'] ?></td>
        <!-- Aqui se coloca los botones para 'actualizar' y 'desincorporar' por el id -->
        <th>
            <!-- Debido que el valor por defecto 'Ninguno' tiene id=1 no se puede hacer ningún tipo de acción que modifique su funcionamiento -->
            <?php if($row['categoria_id']!=1){ ?>
            <button><a href="index.php?vista=form_actualizar_categoria&id=<?= $row['categoria_id'] ?>">Actualizar</a></button>
            <button><a href="index.php?vista=deshabilitar_categoria&id=<?= $row['categoria_id'] ?>" onclick="return confirm('¡¡AVISO!! Eliminar esta categoría hará que todos los registros de esta categoría tengan el valor [Sin categoría]')">Eliminar</a></button>
            <?php } ?>
            <button><a href="listar_por_categoria.php?id=<?= $row['categoria_id'] ?>">Ver Bienes</a></button>
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
<button><a href="index.php?vista=form_registrar_categoria">Nuevo Registro</a></button>
<div class="form-resultado">
        <?php
            if (isset($_SESSION['mensaje'])) {
                echo $_SESSION['mensaje'];
                unset($_SESSION['mensaje']);
            } 
            ?>
</div>