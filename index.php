<?php
session_start();    #Iniciar las sesiones

#Enrutador que determina que página cargar, por defecto el login
$vista = isset($_GET['vista']) ? $_GET['vista'] : 'login';
$archivo = "vistas/$vista.php";
$rutas_publicas = ['login', 'login_registro', 'login_datos'];

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
