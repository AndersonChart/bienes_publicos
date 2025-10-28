<div id="usuario" data-id="<?php echo $_SESSION["rol"]; ?>"></div>
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
                <input type="text" id="correo" name="usuario_correo" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="telefono" class="input_label input-condition">Teléfono</label>
                <input type="text" id="telefono" name="usuario_telefono" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="cedula" class="input_label">Cédula</label>
                <div class="input_text">
                    <select name="tipo_cedula" id="tipo_cedula" class="input_select input_select-cedula">
                    <option value="V">V</option>
                    <option value="E">E</option>
                    </select>
                    <input type="text" name="usuario_cedula" class="input_password" id="numero_cedula">
                </div>
            </div>

            <div class="input_block_content">
                <label for="sexo" class="input_label">Sexo</label>
                <select name="usuario_sexo" id="sexo" class="input_text input_select">
                    <option value="" selected disabled></option>
                    <option value="0">M</option>
                    <option value="1">F</option>
                </select>
            </div>

            <div class="input_block_content">
                <label for="direccion" class="input_label input-condition">Dirección</label>
                <input type="text" name="usuario_direccion" id="direccion" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="nac" class="input_label">Fecha de nacimiento</label>
                <input type="date" name="usuario_nac" id="nac" class="input_text input_date">
            </div>

            <h2 class="modal_subtitle">Credenciales</h2>

            <div class="input_block_content">
                <label for="nombre_usuario" class="input_label">Nombre de Usuario</label>
                <input type="text" id="nombre_usuario" name="usuario_usuario" class="input_text">
            </div>
            <div class="input_block_content">
                <label for="foto" class="input_label input-condition">Foto de Perfil</label>
                <div class="foto_perfil_container">
                    <input type="file" id="foto" name="usuario_foto" class="input_file" accept=".jpg,.jpeg,.png">
                    <div class="foto_perfil_wrapper" onclick="document.getElementById('foto').click()">
                        <img id="preview_foto" class="foto_perfil_imagen" alt="Foto de perfil">
                        <span class="foto_perfil_icon">+</span>
                    </div>
                </div>
            </div>

            <div class="input_block_content">
                <label for="password" class="input_label">Contraseña</label>
                <div class="input_text">
                    <input type="password" name="usuario_clave" class="input_password" >
                    <div class="eye-icon" onclick="togglePassword()"></div>
                </div>
            </div>

            <div class="input_block_content">
                <label for="password_repeat" class="input_label">Repetir Contraseña</label>
                <div class="input_text">
                    <input type="password" name="repetir_clave" class="input_password">
                    <div class="eye-icon" onclick="togglePassword()"></div>
                </div>
            </div>

            <div id="error-container" class="error-container"></div>
            <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar">
        </form>
    </dialog>
    <dialog data-modal="success" class="modal modal_success">
        <form method="dialog">
            </form>
            <div class="modal_icon"></div>
            <h2 class="modal_title">¡Proceso éxitoso!</h2>
            <p class="modal_success-message" id="success-message"></p>
            <button class="modal__close-success" id="close-success">Aceptar</button>
    </dialog>



    <div class="order">Ordenar
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