<button><a href="index.php">Volver</a></button>
<form action="" method="POST" class="FormularioAjax">
    <fieldset>
        <legend>Iniciar sesión</legend>
        <div class="FormularioAjax__input">
            <label for="correo">Ingrese su correo:</label><br>
            <input type="text" id="correo" name="usuario_email">
        </div>
        <div class="FormularioAjax__input">
            <label for="password">Contraseña:</label><br>
            <input type="password" id="password" name="usuario_clave">
        </div>
        <div>
            <a href="index.php?vista=login_registro">No tengo cuenta</a>
            <a href="index.php?vista=login_datos">He olvidado mi usuario/contraseña</a>
        </div>
        <div class="FormularioAjax__input">
            <input type="submit" value="Verificar" name="recuperar">
        </div>
    </fieldset>
</form>
<?php
include "php/verificar_datos.php";
?>