<div id="usuario" data-id="<?php echo $_SESSION["rol"]; ?>"></div>
<div class="banner_list">
    <div class="new_user new_user-usuario" data-modal-target="new_user">+ Nuevo</div>
    <div id="toggleEstado" class="btn_toggle-estado estado-rojo">Deshabilitados</div>
</div>


<!-- Ventana Modal -->
    <!-- Formulario registro/actualización -->
    <dialog data-modal="new_user" class="modal modal_new-user">
        <form method="dialog">
            <button class="modal__close">X</button>
        </form>

        <h2 class="modal_title">Registro de Usuario</h2>
        <p class="condition">Opcional</p>
        <form id="form_nuevo_usuario" method="POST" autocomplete="off" class="user_container">
            
        <input type="hidden" name="usuario_id" id="usuario_id">
        
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
                <input type="text" maxlength="11" id="telefono" name="usuario_telefono" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="cedula" class="input_label">Cédula</label>
                <div class="input_text">
                    <select name="tipo_cedula" id="tipo_cedula" class="input_select input_select-cedula">
                    <option value="V">V</option>
                    <option value="E">E</option>
                    </select>
                    <input type="text" maxlength="8" name="usuario_cedula" class="input_password" id="numero_cedula">
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
                <input type="text" maxlength="100" name="usuario_direccion" id="direccion" class="input_text">
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
                        <span  class="foto_perfil_icon">+</span>
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
    <!-- Mensaje de exito -->
    <dialog data-modal="success" class="modal modal_success">
        <form method="dialog">
            <div class="modal_icon"></div>
            <h2 class="modal_title">¡Proceso éxitoso!</h2>
            <p class="modal_success-message" id="success-message"></p>
            <button class="modal__close-success" id="close-success">Aceptar</button>
        </form>
    </dialog>


    <dialog data-modal="info_usuario" class="modal modal_info">
        <div class="modal_header-info">
            <form method="dialog">
                <button class="modal__close">X</button>
            </form>
            <h2 class="modal_title modal_title-info">Información del Usuario</h2>
        </div>
        <!-- Foto de perfil -->
        <div class="img_info">
            <img id="foto_usuario_info" src="img/icons/perfil.png" alt="Foto de perfil" class="foto_info">
        </div>

        <!-- Contenedor de datos con scroll si excede -->
        <div class="info_container">
            <ul class="info_lista">
                <li><strong class="info_subtitle">Nombre:</strong> <span class="info_data" id="info_nombre"></span></li>
                <li><strong class="info_subtitle">Apellido:</strong> <span class="info_data" id="info_apellido"></span></li>
                <li><strong class="info_subtitle">Correo:</strong> <span class="info_data" id="info_correo"></span></li>
                <li><strong class="info_subtitle">Teléfono:</strong> <span class="info_data" id="info_telefono"></span></li>
                <li><strong class="info_subtitle">Cédula:</strong> <span class="info_data" id="info_cedula"></span></li>
                <li><strong class="info_subtitle">Fecha de nacimiento:</strong> <span class="info_data" id="info_nac"></span></li>
                <li><strong class="info_subtitle">Dirección:</strong> <span class="info_data" id="info_direccion"></span></li>
                <li><strong class="info_subtitle">Sexo:</strong> <span class="info_data" id="info_sexo"></span></li>
                <li><strong class="info_subtitle">Nombre de Usuario:</strong> <span class="info_data" id="info_usuario"></span></li>
            </ul>
        </div>
    </dialog>

    <dialog data-modal="eliminar_usuario" class="modal modal_delete">
        <div class="modal_header-delete">
            <h2 class="modal_title modal_title-delete">¿Estás seguro de deshabilitar <br> este usuario?</h2>
        </div>
        <!-- Foto de perfil -->
        <div class="img_info">
            <img id="delete_foto" src="img/icons/perfil.png" alt="Foto de perfil" class="foto_info">
        </div>
        <!-- Contenedor de usuario relevante -->
        <div class="delete_container">
            <span class="delete_data-title" id="delete_usuario"></span>
            <span class="delete_data" id="delete_nombre"></span>
            <span class="delete_data" id="delete_apellido"></span>
        </div>
        <form method="dialog">
                <button class="modal__close modal__close-delete">Cancelar</button>
        </form>
        <form id="form_delete_usuario" method="POST">
            <input type="submit" value="Aceptar" name="delete" class="register_submit" id="btn_borrar">
        </form>    
        <div id="error-container" class="error-container"></div>
    </dialog>

<div class="container_table_box">
    <div class="top"></div> <!-- Aquí se insertan los botones y búsqueda -->

    <table id="usuarioTabla" class="display" style="width:100%">
    <thead>
        <tr>
            <th colspan="8" class="title">Usuarios</th>
        </tr>
        <tr>
            <th class="header">ID</th>
            <th class="header">Nombre</th>
            <th class="header">Apellido</th>
            <th class="header">Cédula</th>
            <th class="header">Correo</th>
            <th class="header">Teléfono</th>
            <th class="header">Foto</th>
            <th class="header">Acciones</th>
        </tr>
    </thead>
    </table>
    
    
    <div class="bottom paginador"></div>
</div>
    
    
    <script src="js/usuario_datatable.js"></script>