<?php
echo password_hash("admin123", PASSWORD_DEFAULT);
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
        
        <div>
        <a href="index.php?vista=perfil" class="btn btn-outline-danger me-2">Perfil</a>
        <a href="index.php?vista=cerrar_sesion" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    <h4 class="text-secondary mb-3"><?php echo $rol; ?></h4>
    </div>

</div>
</div>









<div class="d-flex flex-column flex-shrink-0 p-3 bg-danger text-white border-end" style="width: 280px; min-height: 100vh;">
    <nav class="nav nav-pills flex-column">
        <?php switch ($_SESSION["rol"]) {

        // Administrador
        case 1: ?>
        <strong class="text-white mb-2">Bienes</strong>
        <nav class="nav flex-column ms-2 mb-3">
            <a href="index.php?vista=listar_bien" class="nav-link text-white">Bienes</a>
            <a href="index.php?vista=listar_categoria" class="nav-link text-white">Categorías</a>
            <a href="index.php?vista=listar_marca" class="nav-link text-white">Marcas</a>
            <a href="index.php?vista=listar_modelo" class="nav-link text-white">Modelos</a>
        </nav>

        <strong class="text-white mb-2">Asignaciones</strong>
        <nav class="nav flex-column ms-2 mb-3">
            <a href="index.php?vista=listar_asignacion" class="nav-link text-white">Asignaciones</a>
            <a href="index.php?vista=listar_persona" class="nav-link text-white">Personas</a>
            <a href="index.php?vista=listar_area" class="nav-link text-white">Áreas</a>
        </nav>

        <a href="index.php?vista=listar_usuario" class="nav-link mb-3 text-white"><strong>Usuarios</strong></a>

        <strong class="text-white mb-2">Auditoría</strong>
        <nav class="nav flex-column ms-2">
            <a href="index.php?vista=reportes" class="nav-link text-white">Reportes</a>
            <a href="index.php?vista=listar_movimiento" class="nav-link text-white">Movimientos</a>
        </nav>
        <?php 
        $rol = "Administrador";
        break;

        // Director
        case 2: ?>
        <strong class="text-white mb-2">Bienes</strong>
        <nav class="nav flex-column ms-2 mb-3">
            <a href="index.php?vista=listar_bien" class="nav-link text-white">Bienes</a>
            <a href="index.php?vista=listar_categoria" class="nav-link text-white">Categorías</a>
            <a href="index.php?vista=listar_marca" class="nav-link text-white">Marcas</a>
            <a href="index.php?vista=listar_modelo" class="nav-link text-white">Modelos</a>
        </nav>

        <strong class="text-white mb-2">Asignaciones</strong>
        <nav class="nav flex-column ms-2 mb-3">
            <a href="index.php?vista=listar_asignacion" class="nav-link text-white">Asignaciones</a>
            <a href="index.php?vista=listar_persona" class="nav-link text-white">Personas</a>
            <a href="index.php?vista=listar_area" class="nav-link text-white">Áreas</a>
        </nav>

        <strong class="text-white mb-2">Auditoría</strong>
        <nav class="nav flex-column ms-2">
            <a href="index.php?vista=reportes" class="nav-link text-white">Reportes</a>
            <a href="index.php?vista=listar_movimiento" class="nav-link text-white">Movimientos</a>
        </nav>
        <?php 
        $rol = "Director";
        break;

        // Usuario Estándar
        case 3: ?>
        <nav class="nav flex-column ms-2">
            <a href="index.php?vista=listar_bien" class="nav-link text-white">Bienes</a>
            <a href="index.php?vista=listar_mi_asignacion" class="nav-link text-white">Mis Asignaciones</a>
        </nav>
        <?php 
        $rol = "Usuario Estándar";
        break;
        } ?>
    </nav>
</div>

