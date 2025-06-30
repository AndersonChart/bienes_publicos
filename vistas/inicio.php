<?php

include ("include/seguridad.php");

include_once("include/banner.php");

echo "<h1>Hola ".$_SESSION["nombre"]." ".$_SESSION["apellido"]."!</h1>";

include_once("include/navbar.php");

?>


<h2><?php echo $rol; ?></h2>

<a href="index.php?vista=cerrar_sesion">Cerrar Sesión</a>

<a href="index.php?vista=perfil">Perfil</a>