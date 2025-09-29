<?php

require_once 'php/usuario.php';
$usuario = new usuario();
$registros = $usuario->leer_todos();
?>

<div class="banner_list">
    <div class="new">+ Nuevo</div>
    <div class="order">Ordenar asc. 
        <div class="order-icon" data-menu="order">
        </div>
    </div>
    <form action="#" method="POST" autocomplete="off" class="buscador">
        <input type="text" name="buscador" class="input_buscar" placeholder="buscar...">
        <button type="submit" class="buscar">
            <img src="img/icons/buscar.png" alt="Buscar">
        </button>
    </form>
</div>

<div class="grid grid-usuario">
    <div class="title title-usuario">Usuarios</div>
        <div class="header">ID</div>
        <div class="header">Nombre</div>
        <div class="header">Apellido</div>
        <div class="header">Cédula</div>
        <div class="header">Correo</div>
        <div class="header">Teléfono</div>
        <div class="header">Foto</div>
        <!-- El siguiente es para los botones 'actualizar' y 'eliminar' -->
        <div class="header">Acciones</div>

    <?php if (!empty($registros)){ 
    
    foreach ($registros as $row){ ?>
        <div class="row"><?= $row['usuario_id'] ?></div>
        <div class="row"><?= $row['usuario_nombre'] ?></div>
        <div class="row"><?= $row['usuario_apellido'] ?></div>
        <div class="row"><?= $row['usuario_cedula'] ?></div>
        <div class="row"><?= $row['usuario_correo'] ?></div>
        <div class="row"><?= $row['usuario_telefono'] ?></div>
        <div class="row"><?= $row['usuario_foto'] ?></div>
        <!-- Aqui se coloca los botones para 'actualizar' y 'eliminar' por el id -->
        <div class="row">
            <!-- Botón Actualizar -->
            <div class="icon-action actualizar" data-url="form_actualizar_usuario.php" data-id="<?= $row['usuario_id'] ?>" data-action="actualizar" title="Actualizar">
                <img src="img/icons/actualizar.png" alt="Actualizar">
            </div>

            <!-- Botón Info -->
            <div class="icon-action info" data-url="info_usuario.php" data-id="<?= $row['usuario_id'] ?>" data-action="info" title="Info">
                <img src="img/icons/info.png" alt="Info">
            </div>

            <!-- Botón Eliminar -->
            <div class="icon-action eliminar" data-id="<?= $row['usuario_id'] ?>" data-action="eliminar" title="Eliminar">
                <img src="img/icons/eliminar.png" alt="Eliminar">
            </div>
        </div>

    <?php }
    }else{  ?>

    <div class="text-empty">No hay ningún registro</div>

    <?php
    }  
    ?>
</div>
<div class="paginador">
    <div class="paginador_info"></div>
    <div class="paginador_opciones">

    </div>
</div>
<!-- Aqui el enlace para seguir creando registros 
<button><a href="index.php?vista=form_registrar_usuario">Nuevo Registro</a></button> -->