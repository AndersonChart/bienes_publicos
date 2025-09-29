<?php
$error = '';

if (!empty($_POST["enviar"])) {
    if (!empty($_POST["usuario_usuario"]) && !empty($_POST["usuario_clave"])) {
        $usuario = $_POST["usuario_usuario"] ?? '';
        $clave = $_POST["usuario_clave"] ?? '';

        $stmt = $pdo->prepare("SELECT usuario.*, rol.rol_nombre FROM usuario INNER JOIN rol ON usuario.rol_id = rol.rol_id WHERE usuario_usuario = :usuario OR usuario_correo = :usuario");
        $stmt->execute([':usuario' => $usuario]);
        $datos = $stmt->fetch(PDO::FETCH_OBJ);

        if ($datos) {
            if (password_verify($clave, $datos->usuario_clave)) {
                $_SESSION["id"] = $datos->usuario_id;
                $_SESSION["nombre"] = $datos->usuario_nombre;
                $_SESSION["apellido"] = $datos->usuario_apellido;
                $_SESSION["rol"] = $datos->rol_id;
                $_SESSION["nombre_rol"] = $datos->rol_nombre;
                header("Location: index.php?vista=inicio");
                exit();
            } else {
                $error = "La contraseña es incorrecta, intente nuevamente";
            }
        } else {
            $error = "Nombre de usuario incorrecto, intente nuevamente";
        }
    }
}
?>

