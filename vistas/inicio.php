<?php
include("include/seguridad.php");
?>

<div class="container-fluid">

<div class="d-flex">

    <div class="bg-danger text-white px-3 pt-3" style="width: 280px; min-height: 100vh;">

    <div class="text-start mb-3">
        <img src="img/logo.png" alt="logito" style="height: 70px;">
    </div>

    <?php include_once("include/navbar.php"); ?>
    </div>

    <div class="flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-danger">
        Hola <?php echo $_SESSION["nombre"] . " " . $_SESSION["apellido"]; ?>!
        </h1>
        <div>
        <a href="index.php?vista=perfil" class="btn btn-outline-danger me-2">Perfil</a>
        <a href="index.php?vista=cerrar_sesion" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    <h4 class="text-secondary mb-3"><?php echo $rol; ?></h4>
    </div>

</div>
</div>

