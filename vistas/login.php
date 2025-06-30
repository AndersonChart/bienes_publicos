<body class="fondo__login">
<form action="" method="POST" class="form">
    <fieldset>
        <legend>Iniciar sesión</legend>
        <div class="form__input">
            <label for="nombre">Nombre de usuario/Correo electrónico:</label><br>
            <input type="text" id="nombre" name="usuario_usuario">
        </div>
        <div class="form__input">
            <label for="password">Contraseña:</label><br>
            <input type="password" id="password" name="usuario_clave">
        </div>
        <div class="form__input">
            <input type="submit" value="Iniciar" name="enviar">
        </div>
        <div>
            <button><a href="index.php?vista=login_registro">No tengo cuenta</a></button>
            <button><a href="index.php?vista=login_datos">He olvidado mi usuario/contraseña</a></button>
        </div>
    </fieldset>
</form>
<?php
include "php/iniciar_sesion.php";
?>