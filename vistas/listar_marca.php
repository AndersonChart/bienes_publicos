<!-- Botones superiores -->
<div class="banner_list">
    <div class="new_user new_user-usuario" data-modal-target="new_marca">+ Nuevo</div>
    <div id="toggleEstado_marca" class="btn_toggle-estado estado-rojo">Deshabilitados</div>
</div>

<!-- Modal: Registro/actualización -->
<dialog data-modal="new_marca" class="modal modal_new-marca">
    <form method="dialog">
        <button class="modal__close">X</button>
    </form>

    <h2 class="modal_title">Registro de Marca</h2>
    <p class="condition">Opcional</p>
    <form id="form_nueva_marca" method="POST" enctype="multipart/form-data" autocomplete="off" class="user_container">
        
        <input type="hidden" name="marca_id" id="marca_id">
        
        <div class="input_block_content">
            <label for="codigo_marca" class="input_label">Código</label>
            <input type="text" maxlength="20" name="marca_codigo" class="input_text" id="codigo_marca" autofocus>
        </div>

        <div class="input_block_content">
            <label for="nombre_marca" class="input_label">Nombre</label>
            <input type="text" id="nombre_marca" name="marca_nombre" class="input_text">
        </div>

        <div class="input_block_content">
            <label for="foto_marca" class="input_label input-condition">Imagen</label>
            <div class="foto_perfil_container">
                <input type="file" id="foto_marca" name="marca_imagen" class="input_file" accept=".jpg,.jpeg,.png">
                <div class="foto_imagen_wrapper" onclick="document.getElementById('foto_marca').click()">
                    <img id="preview_foto_marca" class="foto_imagen" alt="Foto de perfil">
                    <span class="foto_perfil_icon imagen_icon"></span>
                </div>
            </div>
        </div>

        <div id="error_container_marca" class="error-container"></div>
        <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar_marca">
    </form>
</dialog>

<!-- Modal: Información -->
<dialog data-modal="info_marca" class="modal modal_info">
    <div class="modal_header-info">
        <form method="dialog">
            <button class="modal__close">X</button>
        </form>
        <h2 class="modal_title modal_title-info">Información de la marca</h2>
    </div>

    <div class="img_info">
        <img id="marca_imagen_info_marca" class="foto_info imagen_info">
    </div>

    <div class="info_container">
        <ul class="info_lista">
            <li><strong class="info_subtitle">Código:</strong> <span class="info_data" id="info_codigo_marca"></span></li>
            <li><strong class="info_subtitle">Nombre:</strong> <span class="info_data" id="info_nombre_marca"></span></li>
        </ul>
    </div>
</dialog>

<!-- Modal: Éxito -->
<dialog data-modal="success" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon"></div>
        <h2 class="modal_title">¡Proceso éxitoso!</h2>
        <p class="modal_success-message" id="success_message_marca"></p>
        <button class="modal__close-success" id="close_success_marca">Aceptar</button>
    </form>
</dialog>

<!-- Modal: Confirmar eliminación -->
<dialog data-modal="eliminar_marca" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de deshabilitar <br> esta marca?</h2>
    </div>
    <div class="img_info">
        <img id="delete_imagen_marca" class="foto_info imagen_info">
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="delete_codigo_marca"></span>
        <span class="delete_data" id="delete_nombre_marca"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_delete_marca" method="POST">
            <input type="submit" value="Aceptar" name="delete" class="register_submit-confirm" id="btn_borrar_marca">
        </form>
    </div>
</dialog>

<!-- Modal: Confirmar recuperación -->
<dialog data-modal="confirmar_marca" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de recuperar <br> esta marca?</h2>
    </div>
    <div class="img_info">
        <img id="confirmar_imagen_marca" class="foto_info imagen_info">
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="confirmar_codigo_marca"></span>
        <span class="delete_data" id="confirmar_nombre_marca"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_confirmar_marca" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_confirmar_marca">
        </form>
    </div>
</dialog>

<!-- Tabla -->
<div class="container_table_box">
    <div class="top"></div>
    <table id="marcaTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="4" class="title">Marcas</th>
            </tr>
            <tr>
                <th class="header">Código</th>
                <th class="header">Nombre</th>
                <th class="header">Imagen</th>
                <th class="header">Acciones</th>
            </tr>
        </thead>
    </table>
    <div class="bottom paginador"></div>
</div>

<script src="js/marca_datatable.js"></script>
