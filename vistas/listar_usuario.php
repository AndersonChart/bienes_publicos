<?php

require_once 'php/usuario.php';
$usuario = new usuario();
$registros = $usuario->leer_todos();
?>

<div class="banner_list">
    <div class="new_user" data-modal-target="new_user">+ Nuevo</div>
    <!--Ventanas Modales-->
    <dialog data-modal="new_user" class="modal modal_new-user">
        
        <!--Con este form se cierra la ventana modal-->
        <form method="dialog">
            <button class="modal__close">X</button>
        </form>
        <!--Contenido-->
        <h2 class="modal_title">Registro de Usuario</h2>
        
        <p class="condition">Opcional</p>

        <form action="" method="POST" autocomplete="off" class="user_container">
            
            <div class="input_block_content">
                <label for="nombre" class="input_label">Nombre</label>
                <input type="text" id="nombre" name="usuario_nombre" class="input_text" autofocus>
            </div>
            
            <div class="input_block_content">
                <label for="apellido" class="input_label">Apellido</label>
                <input type="text" id="apellido" name="usuario_apellido" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="correo" class="input_label">Correo eléctronico</label>
                <input type="email" id="correo" name="usuario_correo" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="telefono" class="input_label input-condition">Teléfono</label>
                <input type="text" id="telefono" name="usuario_telefono" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="cedula" class="input_label">No. Identidad</label>
                <input type="text" id="cedula" name="usuario_cedula" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="sexo" class="input_label">Sexo</label>
                <select name="usuario_sexo" id="sexo" class="input_select">
                    <option value="0">M</option>
                    <option value="1">F</option>
                </select>
            </div>
            <!--
            <fieldset class="credenciales">
                <legend class="credenciales_title">Credenciales</legend>
                <div class="input_block_content">
                    <label for="nombre_usuario" class="input_label">Nombre de Usuario</label>
                    <input type="text" id="nombre_usuario" name="usuario_usuario" class="input_text">
                </div>

                <div class="input_block_content">
                    <label for="password" class="input_label">Contraseña</label>
                    <div class="password_content">
                        <input type="password" id="password" name="usuario_clave" class="input_password">
                        <div  class="eye-icon" onclick="togglePassword()"></div>
                    </div>
                </div>
            </fieldset>-->
                <!-- 
                <?php //include "php/iniciar_sesion.php"; ?>
                <div id="error-container" class="error-container"><?php// if (!empty($error)) echo $error; ?></div>
                -->
                <input type="submit" value="Guardar" name="save" class="register_submit">
        </form>
    </dialog>
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