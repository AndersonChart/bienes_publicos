<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Panel</h2>
    </div>
    <nav class="sidebar-nav">
        <?php
        switch($_SESSION["rol"]){
            case 1: ?>
                <ul>
                    <li>Bienes</li>
                        <ul>
                            <li><a href="index.php?vista=listar_bien"><i class=""></i> Bienes</a></li>
                            <li><a href="index.php?vista=listar_categoria"><i class=""></i> Categorías</a></li>
                            <li><a href="index.php?vista=listar_marca"><i class=""></i> Marcas</a></li>
                            <li><a href="index.php?vista=listar_modelo"><i class=""></i> Modelos</a></li>
                        </ul>
                    <li>Asignaciones</li>
                        <ul>
                            <li><a href="index.php?vista=listar_asignacion"><i class=""></i> Asignaciones</a></li>
                            <li><a href="index.php?vista=listar_persona"><i class=""></i> Personas</a></li>
                            <li><a href="index.php?vista=listar_area"><i class=""></i> Áreas</a></li>
                        </ul>
                    <li><a href="index.php?vista=listar_usuario"><i class=""></i>Usuarios</a></li>
                    <li>Auditoría</li>
                        <ul>
                            <li><a href="index.php?vista=reportes"><i class=""></i> Reportes</a></li>
                            <li><a href="index.php?vista=listar_movimiento"><i class=""></i> Movimientos</a></li>
                        </ul>
                </ul>
            <?php
                $rol = "Administrador";
                break;
            case 2: //En este caso, el Director podrá leer registros pero no modificarlos?>
                <ul>
                    <li>Bienes</li>
                        <ul>
                            <li><a href="index.php?vista=listar_bien"><i class=""></i> Bienes</a></li>
                            <li><a href="index.php?vista=listar_categoria"><i class=""></i> Categorías</a></li>
                            <li><a href="index.php?vista=listar_bien"><i class=""></i> Marcas</a></li>
                            <li><a href="index.php?vista=listar_bien"><i class=""></i> Modelos</a></li>
                        </ul>
                    <li>Asignaciones</li>
                        <ul>
                            <li><a href="index.php?vista=listar_asignacion"><i class=""></i> Asignaciones</a></li>
                            <li><a href="index.php?vista=listar_persona"><i class=""></i> Personas</a></li>
                            <li><a href="index.php?vista=listar_area"><i class=""></i> Áreas</a></li>
                        </ul>
                    <li>Auditoría</li>
                        <ul>
                            <li><a href="index.php?vista=reportes"><i class=""></i> Reportes</a></li>
                            <li><a href="index.php?vista=listar_movimiento"><i class=""></i> Movimientos</a></li>
                        </ul>
                </ul>
            <?php
                $rol = "Director";
                break;
            case 3: ?>
                <ul>
                    <li><a href="index.php?vista=listar_bien"><i class=""></i> Bienes</a></li>
                    <li><a href="index.php?vista=listar_mi_asignacion"><i class=""></i>Mis Asignaciones</a></li>
                </ul>
            <?php
                $rol = "Usuario Estándar";
                break;
        }
        ?>
    </nav>
</aside>