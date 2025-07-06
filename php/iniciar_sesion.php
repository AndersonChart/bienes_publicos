<?php
// Verificar si se presionó el botón de enviar
if (!empty($_POST["enviar"])) {
    // Verificar si los campos obligatorios están llenos
    if (!empty($_POST["usuario_usuario"]) && !empty($_POST["usuario_clave"])) {
        // Se almacenan los datos en unas variables, sino llegan datos se dejan vacíos
        $usuario = isset($_POST["usuario_usuario"]) ? $_POST["usuario_usuario"] : '';
        $clave = isset($_POST["usuario_clave"]) ? $_POST["usuario_clave"] : '';

        // Usar consulta preparada para evitar inyección SQL
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE usuario_usuario = :usuario OR usuario_email = :usuario");
        $stmt->execute([':usuario' => $usuario]);
        $datos = $stmt->fetch(PDO::FETCH_OBJ);

        // Si encontramos al usuario
        if ($datos) {
            // Verificar la contraseña
            if (password_verify($clave, $datos->usuario_clave)) {
                // Autenticación exitosa, guarda datos en la sesión
                $_SESSION["id"] = $datos->usuario_id;
                $_SESSION["nombre"] = $datos->usuario_nombre;
                $_SESSION["apellido"] = $datos->usuario_apellido;
                $_SESSION["rol"] = $datos->rol_id;
                // Enviar al usuario al inicio
                header("Location: index.php?vista=inicio");
                exit();
            } else {
                echo "<h2>La contraseña es incorrecta, intente nuevamente</h2>";
                exit();
            }
        } else {
            echo "<h2>Nombre de usuario incorrecto o no está registrado, intente nuevamente</h2>";
            exit();
        }
    } else {
        echo "<h2>No ha rellenado todos los campos</h2>";
        exit();
    }
}
?>
