<body class="bg-white d-flex justify-content-center align-items-center vh-100">
    <form action="index.php?action=login" method="POST" autocomplete="off" class="p-4 rounded shadow-sm border" style="width: 100%; max-width: 400px; background-color: #fff;">
        <fieldset>
        <legend class="text-danger text-center fs-4 mb-3">Iniciar sesión</legend>

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de usuario/Correo electrónico</label>
            <input type="text" id="nombre" name="usuario_usuario" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" id="password" name="usuario_clave" class="form-control" required>
        </div>

        <div class="mb-3">
            <input type="submit" value="Iniciar" name="enviar" class="btn btn-danger w-100">
        </div>

        <div class="text-center">
            <a href="index.php?vista=login_registro" class="btn btn-link text-danger">No tengo cuenta</a><br>
            <a href="index.php?vista=login_datos" class="btn btn-link text-danger">He olvidado mi usuario/contraseña</a>
        </div>
        </fieldset>
    </form>

<?php include "php/iniciar_sesion.php"; ?>

