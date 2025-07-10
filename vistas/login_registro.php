<div class="container py-5">
    <div class="row justify-content-center">
    
    <div class="col-md-7 mb-4">
        <form action="" method="POST" class="p-4 border rounded shadow-sm bg-white">
        <h2 class="text-center mb-4 text-danger">Registro de Usuario</h2>

        <fieldset class="mb-3">
            <legend class="fs-5 text-danger">Usuario Estándar</legend>
            <label for="correo_persona" class="form-label">Ingrese su correo</label>
            <input type="email" name="correo_persona" id="correo_persona" class="form-control">
        </fieldset>

        <fieldset class="mb-3">
            <legend class="fs-5 text-danger">Persona Autorizada</legend>
            <label for="clave_admin" class="form-label">Contraseña de administrador</label>
            <input type="password" name="clave_admin" id="clave_admin" class="form-control">
        </fieldset>

        <div class="d-grid">
            <input type="submit" value="Verificar" name="enviar" class="btn btn-danger">
        </div>
        </form>
    </div>

    <div class="col-md-4 d-flex flex-column justify-content-center align-items-center">
        <a href="index.php" class="btn btn-outline-danger mb-3">Volver</a>
    </div>
    </div>

    <?php include("php/verificar_usuario.php");

    if($form==1){ $_SESSION['correo'] = $correo; ?>
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
        <div class="alert alert-warning text-center">Estás registrado como persona, pero no posees cuenta</div>
        <form method="POST" class="p-4 border rounded shadow-sm bg-white">
            <fieldset class="mb-3">
            <legend class="fs-5 text-danger">Nuevo Usuario</legend>
            <label class="form-label">Nombre de Usuario:</label>
            <input type="text" name="usuario_usuario" class="form-control">

            <label class="form-label mt-2">Contraseña:</label>
            <input type="password" name="usuario_clave_1" class="form-control">

            <label class="form-label mt-2">Repetir Contraseña:</label>
            <input type="password" name="usuario_clave_2" class="form-control">

            <label class="form-label mt-2">Foto de Perfil:</label>
            <input type="file" name="usuario_foto" class="form-control">

            <div class="d-grid mt-3">
                <input type="submit" value="Registrar" name="persona_user" class="btn btn-danger">
            </div>
            </fieldset>
        </form>
        </div>
    </div>
    <?php }

    if($form==2){ ?>
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
        <div class="alert alert-danger text-center">No tienes datos en el sistema</div>
        <form method="POST" class="p-4 border rounded shadow-sm bg-white">
            <fieldset>
            <legend class="fs-5 text-danger">Información Personal</legend>
            <label class="form-label">Nombre:</label>
            <input type="text" name="usuario_nombre" class="form-control">

            <label class="form-label mt-2">Apellido:</label>
            <input type="text" name="usuario_apellido" class="form-control">

            <label class="form-label mt-2">Correo:</label>
            <input type="email" name="usuario_email" class="form-control">

            <label class="form-label mt-2">Teléfono (opcional):</label>
            <input type="tel" name="usuario_telefono" class="form-control">
            </fieldset>

            <fieldset class="mt-4">
            <legend class="fs-5 text-danger">Credenciales</legend>
            <label class="form-label">Nombre de Usuario:</label>
            <input type="text" name="usuario_usuario" class="form-control">

            <label class="form-label mt-2">Contraseña:</label>
            <input type="password" name="usuario_clave_1" class="form-control">

            <label class="form-label mt-2">Repetir Contraseña:</label>
            <input type="password" name="usuario_clave_2" class="form-control">

            <label class="form-label mt-2">Foto de Perfil (opcional):</label>
            <input type="file" name="usuario_foto" class="form-control">

            <div class="d-grid mt-3">
                <input type="submit" value="Registrar" name="user_new" class="btn btn-danger">
            </div>
            </fieldset>
        </form>
        </div>
    </div>
    <?php }

    if($form==3){ ?>
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
        <div class="alert alert-success text-center">Está autorizado correctamente</div>
        <form method="POST" class="p-4 border rounded shadow-sm bg-white">
            <fieldset>
            <legend class="fs-5 text-danger">Información Personal</legend>
            <label class="form-label">Nombre:</label>
            <input type="text" name="usuario_nombre" class="form-control">

            <label class="form-label mt-2">Apellido:</label>
            <input type="text" name="usuario_apellido" class="form-control">

            <label class="form-label mt-2">Correo:</label>
            <input type="email" name="usuario_email" class="form-control">

            <label class="form-label mt-2">Teléfono (opcional):</label>
            <input type="tel" name="usuario_telefono" class="form-control">
            </fieldset>

            <fieldset class="mt-4">
            <legend class="fs-5 text-danger">Credenciales</legend>
            <div class="mb-2">
                <span class="form-label">Tipo de Perfil:</span><br>
                <div class="form-check">
                <input type="radio" class="form-check-input" name="rol_id" value="1" id="admin">
                <label class="form-check-label" for="admin">Administrador</label>
                </div>
                <div class="form-check">
                <input type="radio" class="form-check-input" name="rol_id" value="2" id="director">
                <label class="form-check-label" for="director">Director</label>
                </div>
            </div>

            <label class="form-label mt-2">Nombre de Usuario:</label>
            <input type="text" name="usuario_usuario" class="form-control">

            <label class="form-label mt-2">Contraseña:</label>
            <input type="password" name="usuario_clave_1" class="form-control">

            <label class="form-label mt-2">Repetir Contraseña:</label>
            <input type="password" name="usuario_clave_2" class="form-control">

            <label class="form-label mt-2">Foto de Perfil (opcional):</label>
            <input type="file" name="usuario_foto" class="form-control">

            <div class="d-grid mt-3">
                <input type="submit" value="Registrar" name="admin_new" class="btn btn-danger">
            </div>
            </fieldset>
        </form>
        </div>
    </div>
    <?php }

    include("php/nuevo_usuario.php"); ?>
</div>

