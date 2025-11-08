<div class="inicio-container">
    <div class="bienvenida">
        <h1>¡Hola, <?php echo $_SESSION["nombre"] . " " . $_SESSION["apellido"]; ?>!</h1>
        <p>Bienvenido al <strong>Sistema de Asignación de Bienes Públicos</strong></p>
        <p class="frase">“La buena gestión de los recursos es el reflejo del compromiso institucional.”</p>
    </div>

    <div class="funciones">
        <div class="funciones-grid">
            <div class="funcion">
                <img src="img/icons/registro.png" alt="Registro de bienes">
                <h3>Registro de Bienes</h3>
                <p>Permite registrar y clasificar los bienes según su tipo, marca y estado.</p>
            </div>

            <div class="funcion">
                <img src="img/icons/asignacion_bienes.png" alt="Asignación de bienes">
                <h3>Asignación de Bienes</h3>
                <p>Gestiona la asignación de bienes al personal y las distintas áreas de trabajo.</p>
            </div>

            <div class="funcion">
                <img src="img/icons/reporte.png" alt="Generar reportes">
                <h3>Generación de Reportes</h3>
                <p>Crea reportes detallados de bienes registrados, asignados o desincorporados.</p>
            </div>

            <div class="funcion">
                <img src="img/icons/ajuste.png" alt="Control de estado">
                <h3>Control y Estado</h3>
                <p>Permite actualizar el estado de los bienes: disponibles, asignados o en mantenimiento.</p>
            </div>

            <div class="funcion">
                <img src="img/icons/seguridad.png" alt="Seguridad">
                <h3>Seguridad y Usuarios</h3>
                <p>Administra los usuarios del sistema y protege la integridad de los datos registrados.</p>
            </div>

            <div class="funcion">
                <img src="img/icons/recepcion.png" alt="Recepcion">
                <h3>Recepcion</h3>
                <p>Facilita la atención o solicitudes relacionado con los bienes Publicos.</p>
            </div>
        </div>
    </div>
</div>
