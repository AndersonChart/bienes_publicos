<body class="login_background">
    <form action="index.php?action=login" method="POST" autocomplete="off" class="login_container">
        <fieldset class="login_fieldset">
        <legend class="login_title">Iniciar sesión</legend>

        <div class="login_block_content">
            <label for="nombre" class="login_label">Nombre de usuario/Correo electrónico</label>
            <input type="text" id="nombre" name="usuario_usuario" class="login_input" autofocus>
        </div>

        <div class="login_block_content">
            <label for="password" class="login_label">Contraseña</label>
            <div class="login_input">
                <input type="password" id="password" name="usuario_clave" class="login_password">
                <div  class="eye-icon" onclick="togglePassword()"></div>
            </div>
        </div>
        

            <input type="submit" value="Iniciar" name="enviar" class="login_submit">

            <a href="index.php?vista=login_registro" class="login_link">No tengo cuenta</a>

        </fieldset>
    </form>



<?php include "php/iniciar_sesion.php"; ?>

