<div id="usuario" data-id="<?php echo $_SESSION["rol"]; ?>"></div>
<div class="banner_list">
    <div class="filters">
        <!-- Filtro de cargos -->
        <div class="filters">
            <label for="cargo_filtro" class="input_label">Cargo:</label>
            <select name="cargo_id" id="cargo_filtro" class="input_text input_select cargo_filtro">
                <option value="" selected disabled>Seleccione</option>
            </select>
        </div>
    </div>
    <div class="basics-container">
        <div class="new_user new_user-personal" data-modal-target="new_personal">+ Nuevo</div>
        <?php if ($_SESSION["rol"] == 3): ?>
            <div id="toggleEstado" class="btn_toggle-estado estado-rojo">Deshabilitados</div>
        <?php endif; ?>
    </div>
</div>

<!-- Ventanas Modales -->

    <!-- Formulario de registro -->
    <dialog data-modal="new_personal" class="modal modal_new-personal">
        <form method="dialog">
            <button class="modal__close">X</button>
        </form>

        <h2 class="modal_title">Registro de Personal</h2>
        <form id="form_nuevo_personal" method="POST" autocomplete="off" class="user_container">
            
            <input type="hidden" name="persona_id" id="persona_id">

            <div class="input_block_content">
                <label for="persona_nombre" class="input_label">Nombre*</label>
                <input type="text" id="persona_nombre" name="persona_nombre" class="input_text" autofocus>
            </div>

            <div class="input_block_content">
                <label for="persona_apellido" class="input_label">Apellido*</label>
                <input type="text" id="persona_apellido" name="persona_apellido" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="cargo_id" class="input_label">Cargo*</label>
                <select name="cargo_id" id="cargo_id" class="input_text input_select cargo_form">
                    <option value="" selected disabled>Seleccione un cargo</option>
                </select>
            </div>

            <div class="input_block_content">
                <label for="persona_correo" class="input_label">Correo electrónico*</label>
                <input type="text" id="persona_correo" name="persona_correo" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="persona_cedula" class="input_label">Cédula*</label>
                <div class="input_text">
                    <select name="tipo_cedula" id="persona_tipo_cedula" class="input_select input_select-cedula">
                        <option value="V">V</option>
                        <option value="E">E</option>
                    </select>
                    <input type="text" maxlength="8" name="persona_cedula" class="input_password" id="persona_numero_cedula">
                </div>
            </div>

            <div class="input_block_content">
                <label for="persona_sexo" class="input_label">Sexo*</label>
                <select name="persona_sexo" id="persona_sexo" class="input_text input_select">
                    <option value="" selected disabled></option>
                    <option value="0">M</option>
                    <option value="1">F</option>
                </select>
            </div>

            <div class="input_block_content">
                <label for="persona_nac" class="input_label">Fecha de nacimiento*</label>
                <input type="date" name="persona_nac" id="persona_nac" class="input_text input_date">
            </div>

            <div class="input_block_content">
                <label for="persona_telefono" class="input_label">Teléfono</label>
                <input type="text" maxlength="11" id="persona_telefono" name="persona_telefono" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="persona_direccion" class="input_label">Dirección</label>
                <input type="text" maxlength="100" name="persona_direccion" id="persona_direccion" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="persona_foto" class="input_label">Foto del Personal</label>
                <div class="foto_perfil_container">
                    <input type="file" id="persona_foto" name="persona_foto" class="input_file" accept=".jpg,.jpeg,.png">
                    <div class="foto_perfil_wrapper" onclick="document.getElementById('persona_foto').click()">
                        <img id="preview_persona_foto" class="foto_perfil_imagen" alt="Foto de perfil">
                        <span class="foto_perfil_icon"></span>
                    </div>
                </div>
            </div>

            <div id="error-container-persona" class="error-container"></div>
            <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar_persona">
        </form>
    </dialog>

    <!-- Información de registro -->
    <dialog data-modal="info_persona" class="modal modal_info">
        <div class="modal_header-info">
            <form method="dialog">
                <button class="modal__close">X</button>
            </form>
            <h2 class="modal_title modal_title-info">Información del Personal</h2>
        </div>
        <!-- Foto de perfil -->
        <div class="img_info">
            <img id="foto_persona_info" src="img/icons/perfil.png" alt="Foto de perfil" class="foto_info">
        </div>

        <!-- Contenedor de datos con scroll si excede -->
        <div class="info_container">
            <ul class="info_lista">
                <li><strong class="info_subtitle">Nombre:</strong> <span class="info_data" id="info_persona_nombre"></span></li>
                <li><strong class="info_subtitle">Apellido:</strong> <span class="info_data" id="info_persona_apellido"></span></li>
                <li><strong class="info_subtitle">Cargo:</strong> <span class="info_data" id="info_persona_cargo"></span></li>
                <li><strong class="info_subtitle">Sexo:</strong> <span class="info_data" id="info_persona_sexo"></span></li>
                <li><strong class="info_subtitle">Cédula:</strong> <span class="info_data" id="info_persona_cedula"></span></li>
                <li><strong class="info_subtitle">Correo:</strong> <span class="info_data" id="info_persona_correo"></span></li>
                <li><strong class="info_subtitle">Fecha de nacimiento:</strong> <span class="info_data" id="info_persona_nac"></span></li>
                <li><strong class="info_subtitle">Teléfono:</strong> <span class="info_data" id="info_persona_telefono"></span></li>
                <li><strong class="info_subtitle">Dirección:</strong> <span class="info_data" id="info_persona_direccion"></span></li>
            </ul>
        </div>
    </dialog>

<!-- Mensaje de éxito -->
<dialog data-modal="success_persona" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon"></div>
        <h2 class="modal_title">¡Proceso éxitoso!</h2>
        <p class="modal_success-message" id="success-message-persona"></p>
        <button class="modal__close-success" id="close-success-persona">Aceptar</button>
    </form>
</dialog>

<!-- Confirmación de eliminar -->
<dialog data-modal="eliminar_persona" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de deshabilitar <br> este personal?</h2>
    </div>
    <div class="img_info">
        <img id="delete_foto_persona" src="img/icons/perfil.png" alt="Foto de perfil" class="foto_info">
    </div>
    <div class="delete_container">
        <!-- Nombre completo -->
        <span class="delete_data-title" id="delete_persona_nombre_completo"></span>
        <!-- Cargo -->
        <span class="delete_data" id="delete_persona_cargo"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_delete_persona" method="POST">
            <input type="submit" value="Aceptar" name="delete" class="register_submit-confirm" id="btn_borrar_persona">
        </form>
    </div>
</dialog>

<!-- Confirmación de recuperar -->
<dialog data-modal="confirmar_persona" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de recuperar <br> este personal?</h2>
    </div>
    <div class="img_info">
        <img id="confirmar_foto_persona" src="img/icons/perfil.png" alt="Foto de perfil" class="foto_info">
    </div>
    <div class="delete_container">
        <!-- Nombre completo -->
        <span class="delete_data-title" id="confirmar_persona_nombre_completo"></span>
        <!-- Cargo -->
        <span class="delete_data" id="confirmar_persona_cargo"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_confirmar_persona" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_confirmar_persona">
        </form>
    </div>
</dialog>

<!-- Tabla de personal -->
<div class="container_table_box">
    <div class="top"></div> <!-- Aquí se insertan los botones y búsqueda -->

    <table id="personaTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="9" class="title">Personal</th>
            </tr>
            <tr>
                <th class="header">ID</th>
                <th class="header">Nombre</th>
                <th class="header">Apellido</th>
                <th class="header">Cargo</th>
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

<script src="js/personal_datatable.js"></script>