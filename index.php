<?php

session_start();    #Iniciar las sesiones

#Controlar el caché para medidas de seguridad
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

#Enrutador que determina que página cargar, por defecto el login
$vista = isset($_GET['vista']) ? $_GET['vista'] : 'login';
$archivo = "vistas/$vista.php";
$rutas_publicas = ['login', 'login_registro'];

if (!in_array($vista, $rutas_publicas) && empty($_SESSION["id"])) {
    header("Location: index.php?vista=login");
    exit();
}

include "bd/conexion.php"; #¡La conexión primero!
$pdo = Conexion::conectar(); // Obtiene la conexión PDO

include "include/header.php";  #Encabezado, cofiguraciones y más

// Seguridad: solo permite letras, números y guion bajo
if (preg_match('/^[a-zA-Z0-9_]+$/', $vista) && file_exists($archivo)) {
    include $archivo;
}else{
    //En caso de rutas desconocidas, el archivo 404 personalizado se mostrará
    http_response_code(404);
    include "include/404.php";
}
//Pie de página, enlaces de javascript
include "include/footer.php";
?>
