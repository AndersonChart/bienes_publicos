<?php
$form=0;


if (!empty($_POST["enviar"])) {
    $correo = isset($_POST["correo_persona"]) ? $_POST["correo_persona"] : '';
    $clave  = isset($_POST["clave_admin"]) ? $_POST["clave_admin"] : '';

    if (!empty($correo)) {
        // Verificar si ya existe el usuario
        $stmt = $pdo->prepare("SELECT usuario_email FROM usuario WHERE usuario_email = ?");
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            echo "<h2>Ya tiene una cuenta en el sistema</h2>";
            exit;
        } else {
            // Verifica si el correo es de una persona
            $stmt = $pdo->prepare("SELECT persona_email FROM persona WHERE persona_email = ?");
            $stmt->execute([$correo]);
            if ($stmt->fetch()) {
                $form = 1;
            } else {
                $form = 2;
            }
        }
    } elseif (!empty($clave)) {
        // Buscar al usuario administrador
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE rol_id = 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si hay un administrador
        if ($admin && password_verify($clave, $admin['usuario_clave'])) {
            $form = 3;
        } else {
            echo "<h2>La clave es incorrecta</h2>";
            exit;
        }
    }
}