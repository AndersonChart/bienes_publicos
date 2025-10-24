<div class="banner_list">
    <div class="new_user" data-modal-target="new_user">+ Nuevo</div>

    <!-- Ventana Modal -->
    <dialog data-modal="new_user" class="modal modal_new-user">
        <form method="dialog">
            <button class="modal__close">X</button>
        </form>

        <h2 class="modal_title">Registro de Usuario</h2>
        <p class="condition">Opcional</p>

        <form id="form_nuevo_usuario" method="POST" autocomplete="off" class="user_container">
            <div class="input_block_content">
                <label for="nombre" class="input_label">Nombre</label>
                <input type="text" id="nombre" name="usuario_nombre" class="input_text" autofocus>
            </div>

            <div class="input_block_content">
                <label for="apellido" class="input_label">Apellido</label>
                <input type="text" id="apellido" name="usuario_apellido" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="correo" class="input_label">Correo electrónico</label>
                <input type="email" id="correo" name="usuario_correo" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="telefono" class="input_label input-condition">Teléfono</label>
                <input type="text" id="telefono" name="usuario_telefono" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="cedula" class="input_label">No. Identidad</label>
                <input type="text" id="cedula" name="usuario_cedula" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="sexo" class="input_label">Sexo</label>
                <select name="usuario_sexo" id="sexo" class="input_select">
                    <option value="0">M</option>
                    <option value="1">F</option>
                </select>
            </div>

            <h2 class="modal_subtitle">Credenciales</h2>

            <div class="input_block_content">
                <label for="nombre_usuario" class="input_label">Nombre de Usuario</label>
                <input type="text" id="nombre_usuario" name="usuario_usuario" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="password" class="input_label">Contraseña</label>
                <div class="input_text">
                    <input type="password" id="password" name="usuario_clave" class="input_password">
                    <div class="eye-icon" onclick="togglePassword()"></div>
                </div>
            </div>

            <div class="input_block_content">
                <label for="foto" class="input_label input-condition">Foto de Perfil</label>
                <input type="file" id="foto" name="usuario_foto" class="input_file">
                <button class="custom-file-button" onclick="document.getElementById('foto').click()">+</button>
            </div>

            <div class="input_block_content">
                <label for="password_repeat" class="input_label">Repetir Contraseña</label>
                <div class="input_text">
                    <input type="password" id="password_repeat" name="repetir_clave" class="input_password">
                    <div class="eye-icon" onclick="togglePassword()"></div>
                </div>
            </div>

            <div id="error-container" class="error-container"></div>
            <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar">
        </form>
    </dialog>

    <div class="order">Ordenar asc.
        <div class="order-icon" data-menu="order"></div>
    </div>

    <form id="buscador" method="POST" autocomplete="off" class="buscador">
        <input type="text" name="buscador" class="input_buscar" placeholder="buscar...">
        <button type="submit" class="buscar">
            <img src="img/icons/buscar.png" alt="Buscar">
        </button>
    </form>
</div>

<div class="grid grid-usuario" id="grid-usuario">
    <div class="title title-usuario">Usuarios</div>
    <div class="header">ID</div>
    <div class="header">Nombre</div>
    <div class="header">Apellido</div>
    <div class="header">Cédula</div>
    <div class="header">Correo</div>
    <div class="header">Teléfono</div>
    <div class="header">Foto</div>
    <div class="header">Acciones</div>
    <!-- Aquí se insertarán dinámicamente los usuarios vía JS -->
</div>
<div class="paginador">
    <div class="paginador_info"></div>
    <div class="paginador_opciones">

    </div>
</div>