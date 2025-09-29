<header class="inicio-banner">
        <div class="inicio-title-block">
            <div class="inicio-icon"></div>
            <h1 class="inicio-title">bienes</h1>
        </div>
        <div class="inicio-info-block">
            <div class="inicio-info-content">
                <h2 class="inicio-info-title">Nucleo</h2>
                <h3 class="inicio-info-subtitle">Principal</h3>
            </div>
            <div class="inicio-info-content">
                <h2 class="inicio-info-title">Dirección</h2>
                <h3 class="inicio-info-subtitle">Tecnología</h3>
            </div>
        </div>
        <div class="inicio-session-block">
            <div class="inicio-session-content">
                <h2 class="inicio-rol"><?php echo $_SESSION["nombre_rol"]; ?></h2>
                <h3 class="inicio-username"><?php echo $_SESSION["nombre"] ."<br>".$_SESSION["apellido"]; ?></h3>
            </div>
            <div class="inicio-avatar"></div>
            <a class="inicio-link" href="index.php?vista=cerrar_sesion">Cerrar Sesión</a>
        </div>
    </header>