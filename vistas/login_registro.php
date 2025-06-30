<button><a href="index.php">Volver</a></button>
<form action="" method="POST" class="form">
    <h1>Registro de Usuario</h1>
        <fieldset>
            <legend>Usuario Estándar</legend>
            <div>
                <label for="persona">Ingrese su correo para verificar si está registrado en el sistema</label>
                <input type="email" name="correo_persona">
            </div>
        </fieldset>
        <fieldset>
            <legend>Persona Autorizada</legend>
            <div>
                <label for="autorizado">Ingrese la contraseña de administrador</label>
                <input type="password" name="clave_admin">
            </div>
        </fieldset>
        <div class="form__input">
            <input type="submit" value="Verificar" name="enviar">
        </div>
</form>

<?php
include("php/verificar_usuario.php");

if($form==1){
$_SESSION['correo'] = $correo;
?>
<h2>Estás registrado como persona, pero no posees cuenta</h2>
    <form action="" method="POST">
        <fieldset>
            <legend>Nuevo Usuario:</legend>
            <div>
                <label for="">Nombre de Usuario:</label>
                <input type="text" id="" name="usuario_usuario">
            </div>
            <div>
                <label for="">Contraseña:</label>
                <input type="password" id="" name="usuario_clave_1">
            </div>
            <div>
                <label for="">Repetir Contraseña:</label>
                <input type="password" id="" name="usuario_clave_2">
            </div>
            <div>
                <label for="">Foto de Perfil:</label>
                <input type="file" id="" name="usuario_foto">
            </div>
            <div class="">
            <input type="submit" value="Registrar" name="persona_user">
            </div>
        </fieldset>
    </form>

<?php
}
if($form==2){
?>

<h2>No tienes datos en el sistema</h2>
<form action="" id="form_new_user" method="POST">
    <fieldset>
        <legend>Información Personal:</legend>
        <div>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="usuario_nombre">
        </div>
        <div>
            <label for="">Apellido:</label>
            <input type="text" id="apellido" name="usuario_apellido">
        </div>
        <div>
            <label for="email">Correo:</label>
            <input type="email" id="email" name="usuario_email">
        </div>
        <div>
            <label for="tlf">Teléfono (opcional):</label>
            <input type="tel" id="tlf" name="usuario_telefono">
        </div>
    </fieldset>
    <fieldset>
        <legend>Credenciales:</legend>
        <div>
            <label for="usuario">Nombre de Usuario:</label>
            <input type="text" id="usuario" name="usuario_usuario">
        </div>
        <div>
            <label for="">Contraseña:</label>
            <input type="password" id="" name="usuario_clave_1">
        </div>
        <div>
            <label for="">Repetir Contraseña:</label>
            <input type="password" id="" name="usuario_clave_2">
        </div>
        <div>
            <label for="">Foto de Perfil (opcional):</label>
            <input type="file" id="" name="usuario_foto">
        </div>
        <div class="">
        <input type="submit" value="Registrar" name="user_new">
        </div>
    </fieldset>
</form>

<?php
}
if($form==3){
?>

<h2>Está autorizado correctamente</h2>
<form action="" id="form_new_user" method="POST">
    <fieldset>
        <legend>Información Personal:</legend>
        <div>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="usuario_nombre">
        </div>
        <div>
            <label for="">Apellido:</label>
            <input type="text" id="apellido" name="usuario_apellido">
        </div>
        <div>
            <label for="email">Correo:</label>
            <input type="email" id="email" name="usuario_email">
        </div>
        <div>
            <label for="tlf">Teléfono (opcional):</label>
            <input type="tel" id="tlf" name="usuario_telefono">
        </div>
    </fieldset>
    <fieldset>
        <legend>Credenciales:</legend>
        <div>
            <span>Tipo de Perfil:</span><br>
            <input type="radio" id="admin" name="rol_id" value="1">
            <label for="admin">Administrador (Acceso completo a todas las funciones)</label>
            <input type="radio" id="director" name="rol_id" value="2">
            <label for="director">Director (Máxima Auditoría)</label>
        </div>
        <div>
            <label for="usuario">Nombre de Usuario:</label>
            <input type="text" id="usuario" name="usuario_usuario">
        </div>
        <div>
            <label for="">Contraseña:</label>
            <input type="password" id="" name="usuario_clave_1">
        </div>
        <div>
            <label for="">Repetir Contraseña:</label>
            <input type="password" id="" name="usuario_clave_2">
        </div>
        <div>
            <label for="">Foto de Perfil (opcional):</label>
            <input type="file" id="" name="usuario_foto">
        </div>
        <div class="">
        <input type="submit" value="Registrar" name="admin_new">
        </div>
    </fieldset>
</form>

<?php
}

include("php/nuevo_usuario.php");
?>
