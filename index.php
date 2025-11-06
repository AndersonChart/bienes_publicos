<?php
session_start();

# Control de caché
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

# Enrutamiento
$vista = isset($_GET['vista']) ? $_GET['vista'] : 'login';
$archivo = "vistas/$vista.php";
$rutas_publicas = ['login', 'login_registro'];
$esCerrarSesion = ($vista === 'cerrar_sesion');

if (!in_array($vista, $rutas_publicas) && empty($_SESSION["id"])) {
    header("Location: index.php?vista=login");
    exit();
}

# Conexión a BD
include "bd/conexion.php";
$pdo = Conexion::conectar();

# Solo incluir layout si no es cerrar_sesion
if (!$esCerrarSesion) {
    include "include/header.php";
}

# Cargar vista
if (preg_match('/^[a-zA-Z0-9_]+$/', $vista) && file_exists($archivo)) {
    include $archivo;
} else {
    http_response_code(404);
    if (!$esCerrarSesion) {
        include "include/404.php";
    }
}

# Footer solo si no es cerrar_sesion
if (!$esCerrarSesion) {
    include "include/footer.php";
}
?>

