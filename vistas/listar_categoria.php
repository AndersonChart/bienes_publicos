<div id="usuario" data-id="<?php echo $_SESSION["rol"]; ?>"></div>
<div class="banner_list">
    <div class="filters">
        <!-- Filtro -->
        <div class="filters">
                <label for="categoria_tipo_filtro" class="input_label">Tipo:</label>
                <select name="categoria_tipo_filtro" id="categoria_tipo_filtro" class="input_text input_select">
                    <option value="" selected>Todos los tipos</option>
                    <option value="0">Básico</option>
                    <option value="1">Completo</option>
                </select>
        </div>
    </div>
    <div class="basics-container">
        <div class="new_user new_user-usuario" data-modal-target="new_categoria">+ Nuevo</div>
        <?php if ($_SESSION["rol"] == 3): ?>
            <div id="toggleEstado" class="btn_toggle-estado estado-rojo">Deshabilitados</div>
        <?php endif; ?>
    </div>
</div>

<!-- Ventana Modal -->
    <!-- Formulario registro/actualización -->
    <dialog data-modal="new_categoria" class="modal modal_new-categoria">
        <form method="dialog">
            <button class="modal__close">X</button>
        </form>

        <h2 class="modal_title">Registro de categoría</h2>
        <form id="form_nueva_categoria" method="POST" autocomplete="off" class="user_container">
            
        <input type="hidden" name="categoria_id" id="categoria_id">
            
            <div class="input_block_content">
                <label for="codigo" class="input_label">Código*</label>
                <input type="text" maxlength="20" name="categoria_codigo" class="input_text" id="codigo" autofocus>
            </div>

            <div class="input_block_content">
                <label for="nombre" class="input_label">Nombre*</label>
                <input type="text" id="categoria_nombre" name="categoria_nombre" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="categoria_tipo" class="input_label">Tipo*</label>
                <select name="categoria_tipo" id="categoria_tipo" class="input_text input_select">
                    <option value="" selected disabled>Seleccione un tipo</option>
                    <option value="0">Básico - Artículos sin modelo ni marca</option>
                    <option value="1">Completo - Todos los campos</option>
                </select>
            </div>

            <div class="input_block_content">
                <label for="descripcion" class="input_label ">descripción</label>
                <input type="text" id="categoria_descripcion" name="categoria_descripcion" class="input_text">
            </div>

            <div id="error-container-categoria" class="error-container"></div>
            <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar">
        </form>
    </dialog>

    <!-- Información de registro -->
    <dialog data-modal="info_categoria" class="modal modal_info">
        <div class="modal_header-info">
            <form method="dialog">
                <button class="modal__close">X</button>
            </form>
            <h2 class="modal_title modal_title-info">Información de la categoría</h2>
        </div>

        <!-- Contenedor de datos con scroll si excede -->
        <div class="info_container">
            <ul class="info_lista">
                <li><strong class="info_subtitle">Código:</strong> <span class="info_data" id="info_codigo"></span></li>
                <li><strong class="info_subtitle">Nombre:</strong> <span class="info_data" id="info_nombre"></span></li>
                <li><strong class="info_subtitle">Tipo:</strong> <span class="info_data" id="info_tipo"></span></li>
                <li><strong class="info_subtitle">Descripción:</strong> <span class="info_data" id="info_descripcion"></span></li>
            </ul>
        </div>
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

    <!-- Confirmación de eliminar -->
    <dialog data-modal="eliminar_categoria" class="modal modal_confirmar">
        <div class="modal_header-confirmar">
            <h2 class="modal_title">¿Estás seguro de deshabilitar esta categoría? <br>podría ocasionar problemas</h2>
        </div>
        <div class="delete_container">
            <span class="delete_data-title" id="delete_codigo"></span>
            <span class="delete_data" id="delete_nombre"></span>
            <span class="delete_data" id="delete_tipo"></span>
        </div>
        <div class="modal_delete-buttons">
            <form method="dialog">
                <button class="modal__close modal__close-confirm">Cancelar</button>
            </form>
            <form id="form_delete_categoria" method="POST">
                <input type="submit" value="Aceptar" name="delete" class="register_submit-confirm" id="btn_borrar">
            </form>
        </div>
    </dialog>

    <!-- Confirmación de recuperar -->
    <dialog data-modal="confirmar_categoria" class="modal modal_confirmar">
        <div class="modal_header-confirmar">
            <h2 class="modal_title">¿Estás seguro de recuperar <br> esta categoría?</h2>
        </div>
        <div class="delete_container">
            <span class="delete_data-title" id="confirmar_codigo"></span>
            <span class="delete_data" id="confirmar_nombre"></span>
            <span class="delete_data" id="confirmar_tipo"></span>
        </div>
        <div class="modal_delete-buttons">
            <form method="dialog">
                <button class="modal__close modal__close-confirm">Cancelar</button>
            </form>
            <form id="form_confirmar_categoria" method="POST">
                <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_confirmar">
            </form>
        </div>
    </dialog>

<div class="container_table_box">
    <div class="top"></div> <!-- Aquí se insertan los botones y búsqueda -->

    <table id="categoriaTabla" class="display" style="width:100%">
    <thead>
        <tr>
            <th colspan="5" class="title">Categorías</th>
        </tr>
        <tr>
            <th class="header">Código</th>
            <th class="header">Nombre</th>
            <th class="header">Tipo</th>
            <th class="header">Descripción</th>
            <th class="header">Acciones</th>
        </tr>
    </thead>
    </table>
    
    <div class="bottom paginador"></div>
</div>
    
    
    <script src="js/categoria_datatable.js"></script>