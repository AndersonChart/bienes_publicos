<?php
include("include/seguridad.php");
?>

<div class="inicio-background">
    
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
                <h2 class="inicio-rol">Administrador</h2>
                <h3 class="inicio-username">Anderson <br> Chacón</h3>
            </div>
            <div class="inicio-avatar"></div>
            <a class="inicio-link" href="index.php?vista=cerrar_sesion">Cerrar Sesión</a>
        </div>
    </header>
    <div class="main-container">
        <div class="main">
            <div class="icon " data-menu="bienes">
                <img src="img/icons/bienes.png" alt="Bienes" draggable="false">
            </div>
            <div class="icon " data-menu="asignaciones">
                <img src="img/icons/asignacion.png" alt="Asignaciones" draggable="false">
            </div>
            <div class="icon " data-menu="ajustes">
                <img src="img/icons/ajuste.png" alt="Ajustes" draggable="false">
            </div>
            <div class="icon " data-menu="usuarios">
                <img src="img/icons/usuario.png" alt="Usuarios" draggable="false">
            </div>
        </div>
        <div class="sub-main">
            <div class="menu-content" id="bienes">
                <a href="#">Bienes</a>
                <a href="#">Marcas</a>
                <a href="#">Clasificaciones</a>
            </div>
            <div class="menu-content" id="asignaciones">
                <a href="#">Asignaciones</a>
                <a href="#">Areas</a>
                <a href="#">Personal</a>    
            </div>
            <div class="menu-content" id="ajustes">                
                <a href="#">Recepción</a>
                <a href="#">Desincorporación</a>
            </div>
            <div class="menu-content" id="usuarios">
                <a href="#">Usuarios</a>
            </div>
        </div>
    </div>

    <div class="content">
    
    </div>
</div>
<script>
    const icons = document.querySelectorAll('.icon');
    const menus = document.querySelectorAll('.menu-content');

    icons.forEach(icon => {
        icon.addEventListener('click', () => {
        const target = icon.getAttribute('data-menu');

        // 🔹 Desactivar todos los íconos y menús
        icons.forEach(i => i.classList.remove('active'));
        menus.forEach(menu => menu.classList.remove('active'));

        // 🔸 Activar ícono clickeado y su contenido
        icon.classList.add('active');
        const targetMenu = document.getElementById(target);
        if (targetMenu) {
            targetMenu.classList.add('active');
        }
        });
    });
</script>
