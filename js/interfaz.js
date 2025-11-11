//Función global: operación exitosa
function mostrarModalExito(mensaje) {
    const successModal = document.querySelector('dialog[data-modal="success"]');
    const successMessage = document.getElementById('success-message');
    const closeSuccess = document.getElementById('close-success');

    if (successModal && typeof successModal.showModal === 'function') {
        successMessage.textContent = mensaje;
        successModal.showModal();

        closeSuccess.onclick = () => {
            successModal.close();
        };
    }
}

//Editar perfil de usuario al presionar a su foto de perfil
function activarEdicionPerfil() {
    const btn = document.getElementById('btn_editar_perfil');
    if (!btn) return;

    btn.addEventListener('click', () => {
        fetch('php/usuario_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_usuario', id: idUsuarioSesion })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.usuario) {
                const u = data.usuario;
                document.getElementById('usuario_id').value = u.usuario_id;
                document.getElementById('nombre').value = u.usuario_nombre;
                document.getElementById('apellido').value = u.usuario_apellido;
                document.getElementById('correo').value = u.usuario_correo;
                document.getElementById('telefono').value = u.usuario_telefono;
                document.getElementById('tipo_cedula').value = u.usuario_cedula.charAt(0);
                document.getElementById('numero_cedula').value = u.usuario_cedula.slice(2);
                document.getElementById('sexo').value = u.usuario_sexo;
                document.getElementById('direccion').value = u.usuario_direccion;
                document.getElementById('nac').value = u.usuario_nac;
                document.getElementById('nombre_usuario').value = u.usuario_usuario;

                const foto = u.usuario_foto || 'img/icons/perfil.png';
                const fotoConTimestamp = foto + '?t=' + new Date().getTime();

                // Actualizar previsualización en el formulario
                document.getElementById('preview_foto').src = fotoConTimestamp;

                // Actualizar avatar del header
                const avatarHeader = document.getElementById('foto_usuario_header');
                if (avatarHeader) avatarHeader.src = fotoConTimestamp;

                const modal = document.querySelector('dialog[data-modal="new_user"]');
                if (modal?.showModal) modal.showModal();
            }
        });
    });
}

//Para mantener la foto de perfil
function mantenerFotoUsuarioActualizada() {
    const avatar = document.getElementById('foto_usuario_header');
    if (!avatar) return;

    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_usuario', id: idUsuarioSesion })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito && data.usuario) {
            const nuevaFoto = data.usuario.usuario_foto || 'img/icons/perfil.png';
            const actual = avatar.getAttribute('src').split('?')[0];

            if (actual !== nuevaFoto) {
                avatar.src = nuevaFoto + '?t=' + new Date().getTime();
            }
        }
    });
}

function cargarCategorias(opciones = {}) {
    fetch('php/categoria_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'leer_todas' })
    })
    .then(res => res.json())
    .then(data => {
        if (!Array.isArray(data)) {
            console.error('Respuesta inválida al cargar categorías:', data);
            return;
        }

        // Selects para filtros
        document.querySelectorAll('select.categoria_filtro').forEach(select => {
            select.innerHTML = '';
            const todasOption = document.createElement('option');
            todasOption.value = '';
            todasOption.textContent = 'Todas las categorías';
            select.appendChild(todasOption);

            data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.categoria_id;
                option.textContent = cat.categoria_nombre;
                select.appendChild(option);
            });
        });

        // Selects para formularios
        document.querySelectorAll('select.categoria_form').forEach(select => {
            select.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.disabled = true;
            defaultOption.selected = true;
            defaultOption.textContent = 'Seleccione una categoría';
            select.appendChild(defaultOption);

            data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.categoria_id;
                option.textContent = cat.categoria_nombre;
                select.appendChild(option);
            });
        });

        if (typeof opciones.onComplete === 'function') {
            opciones.onComplete(data);
        }
    })
    .catch(err => {
        console.error('Error al cargar categorías:', err);
    });
}

function cargarClasificacion(opciones = {}) {
    const params = new URLSearchParams({ accion: 'leer_todos' });

    // Si se pasa una categoría, se incluye en la petición
    if (opciones.categoria_id) {
        params.append('categoria_id', opciones.categoria_id);
    }

    fetch('php/clasificacion_ajax.php', {
        method: 'POST',
        body: params
    })
    .then(res => res.json())
    .then(data => {
        const lista = Array.isArray(data.data) ? data.data : [];

        if (lista.length === 0) {
            console.warn('No se recibieron clasificaciones válidas:', data);
            return;
        }

        // Selects para filtros (ej. en la parte superior de la vista)
        document.querySelectorAll('select.clasificacion_filtro').forEach(select => {
            select.innerHTML = '';
            const todasOption = document.createElement('option');
            todasOption.value = '';
            todasOption.textContent = 'Todas las clasificaciones';
            select.appendChild(todasOption);

            lista.forEach(clasificacion => {
                const option = document.createElement('option');
                option.value = clasificacion.clasificacion_id;
                option.textContent = clasificacion.clasificacion_nombre;
                select.appendChild(option);
            });
        });

        // Selects para formularios (ej. dentro de modales)
        document.querySelectorAll('select.clasificacion_form').forEach(select => {
            select.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.disabled = true;
            defaultOption.selected = true;
            defaultOption.textContent = 'Seleccione una clasificación';
            select.appendChild(defaultOption);

            lista.forEach(clasificacion => {
                const option = document.createElement('option');
                option.value = clasificacion.clasificacion_id;
                option.textContent = clasificacion.clasificacion_nombre;
                select.appendChild(option);
            });
        });

        // Callback opcional
        if (typeof opciones.onComplete === 'function') {
            opciones.onComplete(lista);
        }
    })
    .catch(err => {
        console.error('Error al cargar clasificación:', err);
    });
}

function cargarMarca(opciones = {}) {
    const params = new URLSearchParams({ accion: 'leer_todos' });

    fetch('php/marca_ajax.php', {
        method: 'POST',
        body: params
    })
    .then(res => res.json())
    .then(data => {
        const lista = Array.isArray(data.data) ? data.data : [];

        if (lista.length === 0) {
            console.warn('No se recibieron marcas válidas:', data);
            return;
        }

        // Selects para filtros (ej. en la parte superior de la vista)
        document.querySelectorAll('select.marca_filtro').forEach(select => {
            select.innerHTML = '';
            const todasOption = document.createElement('option');
            todasOption.value = '';
            todasOption.textContent = 'Todas las marcas';
            select.appendChild(todasOption);

            lista.forEach(marca => {
                const option = document.createElement('option');
                option.value = marca.marca_id;
                option.textContent = marca.marca_nombre;
                select.appendChild(option);
            });
        });

        // Selects para formularios (ej. dentro de modales)
        document.querySelectorAll('select.marca_form').forEach(select => {
            select.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.disabled = true;
            defaultOption.selected = true;
            defaultOption.textContent = 'Seleccione una marca';
            select.appendChild(defaultOption);

            lista.forEach(marca => {
                const option = document.createElement('option');
                option.value = marca.marca_id;
                option.textContent = marca.marca_nombre;
                select.appendChild(option);
            });
        });

        // Callback opcional
        if (typeof opciones.onComplete === 'function') {
            opciones.onComplete(lista);
        }
    })
    .catch(err => {
        console.error('Error al cargar marca:', err);
    });
}



//Función global: Limpiar formularios
function limpiarFormulario(form) {
    if (!form) return;

    // Resetear campos del formulario (general)
    form.reset();

    // Quitar clases de error visual (general)
    form.querySelectorAll('.input-error').forEach(el => {
        el.classList.remove('input-error');
    });

    // Limpiar contenedores de error si existen (general)
    form.querySelectorAll('.error-container').forEach(container => {
        container.innerHTML = '';
        container.style.display = 'none';
    });

    // Limpiar imágenes de previsualización ( usado en usuario: foto de perfil)
    form.querySelectorAll('img[id^="preview_"]').forEach(img => {
        img.removeAttribute('src');
        img.style.display = 'none';
    });

    // Restaurar íconos visuales si existen ( usado en usuario: ícono de foto)
    form.querySelectorAll('.foto_perfil_icon').forEach(icono => {
        icono.style.opacity = '1';
    });

    // Limpiar campos ocultos tipo ID ( usado en usuario: usuario_id)
    form.querySelectorAll('input[type="hidden"]').forEach(input => {
        input.value = '';
    });
}

//  Función específica del módulo de usuario
function limpiarInfoUsuario() {
    // Limpiar todos los campos de texto ( usado en modal de info de usuario)
    document.querySelectorAll('.info_data').forEach(el => {
        el.textContent = '';
    });

    // Restaurar imagen de perfil por defecto ( usado en modal de info de usuario)
    const foto = document.getElementById('foto_usuario_info');
    if (foto) {
        foto.src = 'img/icons/perfil.png';
    }
}

function limpiarFormularioMarca() {
    const form = document.getElementById('form_nueva_marca');
    if (!form) return;

    form.reset();

    form.querySelectorAll('.input-error').forEach(el => {
        el.classList.remove('input-error');
    });

    const errorContainer = document.getElementById('error_container_marca');
    if (errorContainer) {
        errorContainer.innerHTML = '';
        errorContainer.style.display = 'none';
    }

    const preview = document.getElementById('preview_foto_marca');
    if (preview) {
        preview.removeAttribute('src');
        preview.style.display = 'none';
    }

    const idInput = document.getElementById('marca_id');
    if (idInput) {
        idInput.value = '';
    }
}

function limpiarFormularioBien() {
    const form = document.getElementById('form_nuevo_bien');
    if (!form) return;

    // Resetear campos del formulario
    form.reset();

    // Quitar clases de error visual
    form.querySelectorAll('.input-error').forEach(el => {
        el.classList.remove('input-error');
    });

    // Limpiar contenedor de error específico
    const errorContainer = document.getElementById('error-container-clasificacion');
    if (errorContainer) {
        errorContainer.innerHTML = '';
        errorContainer.style.display = 'none';
    }

    // Limpiar imagen de previsualización
    const preview = document.getElementById('preview_foto');
    if (preview) {
        preview.removeAttribute('src');
        preview.style.display = 'none';
    }

    // Restaurar ícono visual
    const icono = form.querySelector('.foto_perfil_icon');
    if (icono) {
        icono.style.opacity = '1';
    }

    // Limpiar campos ocultos tipo ID
    const idInput = document.getElementById('bien_tipo_id');
    if (idInput) {
        idInput.value = '';
    }

    // Restaurar selects al estado inicial
    form.querySelectorAll('select').forEach(select => {
        select.selectedIndex = 0;
    });
}




//Mostrar contraseña al presionar el ojo
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("eye-icon")) {
        const container = e.target.closest(".input_text");
        const input = container.querySelector(".input_password");

        if (!input) {
            console.error("No se encontró el input asociado al eye-icon");
            return;
        }

        const isHidden = input.type === "password";
        input.type = isHidden ? "text" : "password";
        e.target.classList.toggle("visible", isHidden);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const icons = document.querySelectorAll('.icon');
    const menus = document.querySelectorAll('.menu-content');

    icons.forEach(icon => {
        icon.addEventListener('click', () => {
            const target = icon.getAttribute('data-menu');
            icons.forEach(i => i.classList.remove('active'));
            menus.forEach(menu => menu.classList.remove('active'));
            icon.classList.add('active');
            const targetMenu = document.getElementById(target);
            if (targetMenu) {
                targetMenu.classList.add('active');
            }
        });
    });

    document.querySelectorAll('.modal__close').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('dialog');
            if (!modal) return;

            const form = modal.querySelector('form[id]');
            if (form) limpiarFormulario(form);

            modal.close();
        });
    });

    // Función para mostrar/ocultar contraseña
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const eyeIcon = document.querySelector(".eye-icon");

        if (!passwordInput) {
            console.error("No se encontró el input de contraseña");
            return;
        }

        const isHidden = passwordInput.type === "password";
        passwordInput.type = isHidden ? "text" : "password";
        eyeIcon.classList.toggle("visible", isHidden);
    }

    // Delegación global para cualquier .eye-icon
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("eye-icon")) {
            togglePassword();
        }
    });

    //Para abrir ventanas modales que tengan el mismo data-modal-target
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('[data-modal-target]');
        if (boton) {
            const modalID = boton.getAttribute('data-modal-target');
            const modal = document.querySelector(`[data-modal="${modalID}"]`);
            if (modal) {
                modal.showModal();

                const form = modal.querySelector('form[id]');
                if (form) limpiarFormulario(form);
            }
        }
    });

    const btncrear = document.getElementById('btn_crear');
    if (btncrear) btncrear.disabled = false;

    cargarCategorias();
    cargarClasificacion();
    cargarMarca();
    mantenerFotoUsuarioActualizada();
});

//Al cargar que pueda hacer las siguiente funciones
window.addEventListener('load', () => {
    activarEdicionPerfil();
});


/*MODULO USUARIO*/

//Función: formulario de actualizar usuario
function abrirFormularioEdicionUsuario(id) {
    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_usuario', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.usuario) return;

        const u = data.usuario;
        const form = document.getElementById('form_nuevo_usuario');
        if (!form) return;

        form.usuario_id.value = u.usuario_id;
        form.nombre.value = u.usuario_nombre;
        form.apellido.value = u.usuario_apellido;
        form.correo.value = u.usuario_correo;
        form.telefono.value = u.usuario_telefono;
        form.usuario_usuario.value = u.usuario_usuario;
        form.direccion.value = u.usuario_direccion;
        form.nac.value = u.usuario_nac;
        form.sexo.value = u.usuario_sexo;
        form.tipo_cedula.value = u.usuario_cedula.split('-')[0];
        document.getElementById('numero_cedula').value = u.usuario_cedula.split('-')[1];

        // Previsualizar foto
        const previewFoto = document.getElementById('preview_foto');
        const icono = document.querySelector('.foto_perfil_icon');
        previewFoto.src = u.usuario_foto || 'img/icons/perfil.png';
        previewFoto.style.display = 'block';
        icono.style.opacity = '0';

        const modal = document.querySelector('[data-modal="new_user"]');
        if (modal) modal.showModal();
    });
}


//Crear/actualizar usuario
document.addEventListener('DOMContentLoaded', function () {
    const inputFoto = document.getElementById('foto');
    const previewFoto = document.getElementById('preview_foto');
    const icono = document.querySelector('.foto_perfil_icon');
    const form = document.getElementById('form_nuevo_usuario');
    const errorContainer = document.getElementById('error-container');

    // Previsualizar imagen
    inputFoto.addEventListener('change', function () {
        const archivo = this.files[0];
        if (archivo) {
            const lector = new FileReader();
            lector.onload = function (e) {
                previewFoto.src = e.target.result;
                previewFoto.style.display = 'block';
                icono.style.opacity = '0';
            };
            lector.readAsDataURL(archivo);
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const tipo = document.getElementById('tipo_cedula').value;
        const numeroInput = document.getElementById('numero_cedula');
        const numero = numeroInput.value.trim();
        const usuarioId = document.getElementById('usuario_id').value;

        const cedulaCompleta = tipo + '-' + numero;
        const formData = new FormData(form);
        formData.set('usuario_cedula', cedulaCompleta);

        // Solo asignar rol si estás creando
        if (!usuarioId) {
            formData.append('rol_id', '1');
        }

        // Acción condicional
        const accion = usuarioId ? 'actualizar' : 'crear';
        formData.append('accion', accion);

        if (usuarioId) formData.append('usuario_id', usuarioId);

        fetch('php/usuario_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            errorContainer.innerHTML = '';
            errorContainer.style.display = 'none';

            if (data.error) {
                errorContainer.innerHTML = `<p>${data.mensaje}</p>`;
                errorContainer.style.display = 'block';

                if (data.campos && Array.isArray(data.campos)) {
                    data.campos.forEach((campo, index) => {
                        let input = form.querySelector(`[name="${campo}"]`);
                        if (campo === 'usuario_cedula') input = document.getElementById('numero_cedula');
                        if (campo === 'usuario_foto') input = inputFoto;
                        if (input) {
                            const contenedor = input.closest('.input_text');
                            if (contenedor) {
                                contenedor.classList.add('input-error');
                            } else {
                                input.classList.add('input-error');
                            }
                            if (index === 0) input.focus();
                        }
                    });
                }
            } else if (data.exito) {
                const esActualizacion = !!usuarioId;
                const mensaje = esActualizacion ? "Usuario actualizado con éxito" : "Usuario registrado con éxito";

                // Solo cerrar el modal si fue una actualización
                if (esActualizacion) {
                    const modalFormulario = document.querySelector('dialog[data-modal="new_user"]');
                    if (modalFormulario && modalFormulario.open) {
                        modalFormulario.close();
                    }
                }

                mostrarModalExito(mensaje);

                // Si el usuario actualizado es el que está en sesión, refrescar su foto en el header
                if (usuarioId && usuarioId === String(idUsuarioSesion)) {
                    fetch('php/usuario_ajax.php', {
                        method: 'POST',
                        body: new URLSearchParams({ accion: 'obtener_usuario', id: usuarioId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.exito && data.usuario) {
                            const nuevaFoto = data.usuario.usuario_foto || 'img/icons/perfil.png';
                            const avatarHeader = document.getElementById('foto_usuario_header');
                            if (avatarHeader) {
                                avatarHeader.src = nuevaFoto + '?t=' + new Date().getTime();
                            }
                        }
                    });
                }

                // Limpiar formulario para siguiente registro
                limpiarFormulario(form);
                // Recarga la tabla
                $('#usuarioTabla').DataTable().ajax.reload(null, false);
            }
        })
        .catch(() => {
            errorContainer.innerHTML = 'Hubo un error con el servidor';
            errorContainer.style.display = 'block';
        });
    });
});


//funcion INFO USUARIO
function mostrarInfoUsuario(data) {
    // Foto de perfil
    const foto = data.usuario_foto && data.usuario_foto.trim() !== ''
        ? data.usuario_foto
        : 'img/icons/perfil.png';
    document.getElementById('foto_usuario_info').src = foto;

    // Formatear fecha de nacimiento
    let fechaFormateada = '';
    if (data.usuario_nac) {
        const fecha = new Date(data.usuario_nac);
        const dia = String(fecha.getDate()).padStart(2, '0');
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const año = fecha.getFullYear();
        fechaFormateada = `${dia}-${mes}-${año}`;
    }

    // Traducir sexo binario
        const sexo = Number(data.usuario_sexo);
        const sexoTraducido =   sexo === 0 ? 'M' :
                                sexo === 1 ? 'F' : '';


    // Datos personales
    document.getElementById('info_nombre').textContent = data.usuario_nombre || '';
    document.getElementById('info_apellido').textContent = data.usuario_apellido || '';
    document.getElementById('info_correo').textContent = data.usuario_correo || '';
    document.getElementById('info_telefono').textContent = data.usuario_telefono || '';
    document.getElementById('info_cedula').textContent = data.usuario_cedula || '';
    document.getElementById('info_nac').textContent = fechaFormateada;
    document.getElementById('info_direccion').textContent = data.usuario_direccion || '';
    document.getElementById('info_sexo').textContent = sexoTraducido;
    document.getElementById('info_usuario').textContent = data.usuario_usuario || '';

    // Mostrar el modal
    const modal = document.querySelector('dialog[data-modal="info_usuario"]');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
}

// Activar botón "Info" en cada fila usuario
$('#usuarioTabla tbody').on('click', '.btn_ver_info', function () {
    const id = $(this).data('id');
    if (!id) return;

    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_usuario', id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito && data.usuario) {
            mostrarInfoUsuario(data.usuario);
        }
    });
});

//Limpiar modal info usuario
document.querySelector('.modal__close')?.addEventListener('click', function () {
    const modal = document.querySelector('dialog[data-modal="info_usuario"]');
    if (modal && modal.open) {
        modal.close();
        limpiarInfoUsuario();
    }
});



//funcion: mostrar datos en confirmacion
function mostrarConfirmacionUsuario(data, modo = 'eliminar') {
    const foto = data.usuario_foto?.trim() !== '' ? data.usuario_foto : 'img/icons/perfil.png';
    document.getElementById('confirmar_foto').src = foto;

    document.getElementById('confirmar_nombre_completo').textContent =
        `${data.usuario_nombre || ''} ${data.usuario_apellido || ''}`.trim();

    document.getElementById('confirmar_usuario').textContent = data.usuario_usuario || '';

    const form = document.getElementById('form_confirmar_usuario');
    form.dataset.usuarioId = data.usuario_id;
    form.dataset.modo = modo;

    const modal = document.querySelector('dialog[data-modal="confirmar_usuario"]');
    if (modal?.showModal) modal.showModal();
}


//funcion: Eliminar/recuperar Usuario
document.getElementById('form_confirmar_usuario')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.usuarioId;
    const modo = this.dataset.modo;
    if (!id || !modo) return;

    const errorContainer = document.getElementById('error-container-confirmar');
    errorContainer.textContent = '';
    errorContainer.style.display = 'none';

    const accion = modo === 'recuperar' ? 'recuperar_usuario' : 'deshabilitar_usuario';

    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion, id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            // Cerrar primero el modal de confirmación
            const modalConfirmar = document.querySelector('dialog[data-modal="confirmar_usuario"]');
            if (modalConfirmar?.open) modalConfirmar.close();

            // Mostrar modal de éxito
            mostrarModalExito(data.mensaje || 'Operación completada');

            // Recargar tabla
            $('#usuarioTabla').DataTable().ajax.reload(null, false);
        } else {
            errorContainer.textContent = data.mensaje || 'Error inesperado';
            errorContainer.style.display = 'block';
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});


/* SUBMODULO CLASIFICACION */

// Función: formulario de actualizar clasificación
function abrirFormularioEdicionClasificacion(id) {
    const form = document.getElementById('form_nueva_clasificacion');
    if (!form) return;

    // Obtener datos de la clasificación
    fetch('php/clasificacion_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_clasificacion', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.clasificacion) return;

        const c = data.clasificacion;

        // Cargar categorías y luego asignar el valor
        cargarCategorias({
            onComplete: () => {
                form.clasificacion_id.value = c.clasificacion_id;
                form.codigo.value = c.clasificacion_codigo;
                form.nombre.value = c.clasificacion_nombre;
                form.descripcion.value = c.clasificacion_descripcion;

                const categoriaSelect = form.querySelector('[name="categoria_id"]');
                if (categoriaSelect) categoriaSelect.value = c.categoria_id;

                const modal = document.querySelector('[data-modal="new_clasificacion"]');
                if (modal?.showModal) modal.showModal();
            }
        });
    })
    .catch(err => {
        console.error('Error al obtener clasificación:', err);
    });
}

// Crear o actualizar clasificación
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form_nueva_clasificacion');
    const errorContainer = document.getElementById('error-container-clasificacion');
    const btnNuevo = document.querySelector('[data-modal-target="new_clasificacion"]');
    const modalFormulario = document.querySelector('dialog[data-modal="new_clasificacion"]');

    // Abrir modal y cargar categorías
    if (btnNuevo && modalFormulario) {
        btnNuevo.addEventListener('click', () => {
            if (modalFormulario.showModal) modalFormulario.showModal();
            cargarCategorias();
            limpiarFormulario(form);
        });
    }

    // Envío del formulario
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const clasificacionId = document.getElementById('clasificacion_id').value;
            const formData = new FormData(form);
            const accion = clasificacionId ? 'actualizar' : 'crear';

            formData.append('accion', accion);
            if (clasificacionId) formData.append('clasificacion_id', clasificacionId);

            fetch('php/clasificacion_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                errorContainer.innerHTML = '';
                errorContainer.style.display = 'none';

                if (data.error) {
                    errorContainer.innerHTML = `<p>${data.mensaje}</p>`;
                    errorContainer.style.display = 'block';

                    if (data.campos && Array.isArray(data.campos)) {
                        data.campos.forEach((campo, index) => {
                            const input = form.querySelector(`[name="${campo}"]`);
                            if (input) {
                                input.classList.add('input-error');
                                if (index === 0) input.focus();
                            }
                        });
                    }
                } else if (data.exito) {
                    const esActualizacion = !!clasificacionId;
                    const mensaje = esActualizacion
                        ? "Clasificación actualizada con éxito"
                        : "Clasificación registrada con éxito";

                    if (modalFormulario && modalFormulario.open) {
                        modalFormulario.close();
                    }

                    mostrarModalExito(mensaje);
                    limpiarFormulario(form);
                    $('#clasificacionTabla').DataTable().ajax.reload(null, false);
                }
            })
            .catch(() => {
                errorContainer.innerHTML = 'Hubo un error con el servidor';
                errorContainer.style.display = 'block';
            });
        });
    }
});


// Mostrar información de clasificación
function mostrarInfoClasificacion(data) {
    document.getElementById('info_codigo').textContent = data.clasificacion_codigo || '';
    document.getElementById('info_nombre').textContent = data.clasificacion_nombre || '';
    document.getElementById('info_descripcion').textContent = data.clasificacion_descripcion || '';
    document.getElementById('info_categoria').textContent = data.categoria_nombre || '';

    const modal = document.querySelector('dialog[data-modal="info_clasificacion"]');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
}

// Limpiar modal info clasificación
document.querySelector('dialog[data-modal="info_clasificacion"] .modal__close')?.addEventListener('click', function () {
    const modal = document.querySelector('dialog[data-modal="info_clasificacion"]');
    if (modal && modal.open) {
        modal.close();
    }
});

// Mostrar datos en confirmación
function mostrarConfirmacionclasificacion(data, modo = 'eliminar') {
    if (modo === 'eliminar') {
        document.getElementById('delete_codigo').textContent = data.clasificacion_codigo || '';
        document.getElementById('delete_nombre').textContent = data.clasificacion_nombre || '';
        document.getElementById('delete_categoria').textContent = data.categoria_nombre || '';

        const form = document.getElementById('form_delete_clasificacion');
        form.dataset.clasificacionId = data.clasificacion_id;
        form.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="eliminar_clasificacion"]');
        if (modal?.showModal) modal.showModal();
    } else if (modo === 'recuperar') {
        document.getElementById('confirmar_codigo').textContent = data.clasificacion_codigo || '';
        document.getElementById('confirmar_nombre').textContent = data.clasificacion_nombre || '';
        document.getElementById('confirmar_categoria').textContent = data.categoria_nombre || '';

        const form = document.getElementById('form_confirmar_clasificacion');
        form.dataset.clasificacionId = data.clasificacion_id;
        form.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="confirmar_clasificacion"]');
        if (modal?.showModal) modal.showModal();
    }
}


// Eliminar clasificación
document.getElementById('form_delete_clasificacion')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.clasificacionId;
    if (!id) return;

    fetch('php/clasificacion_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'deshabilitar_clasificacion', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="eliminar_clasificacion"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Clasificación deshabilitada');
            $('#clasificacionTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });

});

//Recuperar clasificacion
document.getElementById('form_confirmar_clasificacion')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.clasificacionId;
    if (!id) return;

    fetch('php/clasificacion_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'recuperar_clasificacion', id })
    })
    .then(res => res.json())
    .then(data => {
        console.log('Respuesta recuperación:', data); // Verifica que se ejecuta
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="confirmar_clasificacion"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Clasificación recuperada');

            estadoActual = 1;
            $('#clasificacionTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});
//Filtros de categorias
document.addEventListener('change', function (e) {
    if (e.target.matches('#categoria_filtro')) {
        $('#clasificacionTabla').DataTable().ajax.reload(null, false);
    }
});


/* SUBMODULO MARCA */

// Función: formulario de actualizar marca
function abrirFormularioEdicionMarca(id) {
    const form = document.getElementById('form_nueva_marca');
    if (!form) return;

    fetch('php/marca_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_marca', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.marca) return;

        const m = data.marca;
        form.querySelector('#marca_id').value = m.marca_id;
        form.querySelector('#codigo_marca').value = m.marca_codigo;
        form.querySelector('#nombre_marca').value = m.marca_nombre;

        const preview = document.getElementById('preview_foto_marca');
        const icono = document.querySelector('.foto_perfil_icon');

        if (m.marca_imagen && m.marca_imagen.trim() !== '') {
            preview.src = m.marca_imagen + '?t=' + new Date().getTime();
            preview.style.display = 'block';
            icono.style.opacity = '0';
        } else {
            preview.src = '';
            preview.style.display = 'none';
            icono.style.opacity = '1';
        }

        const modal = document.querySelector('[data-modal="new_marca"]');
        if (modal?.showModal) modal.showModal();
    })
    .catch(err => {
        console.error('Error al obtener marca:', err);
    });
}

// Crear o actualizar marca
document.addEventListener('DOMContentLoaded', function () {
    const formMarca = document.getElementById('form_nueva_marca');
    const errorContainerMarca = document.getElementById('error_container_marca');
    const inputFotoMarca = document.getElementById('foto_marca');
    const previewFotoMarca = document.getElementById('preview_foto_marca');
    const iconoMarca = formMarca?.querySelector('.foto_perfil_icon');

    // Previsualizar imagen de marca
    if (inputFotoMarca && previewFotoMarca && iconoMarca) {
        inputFotoMarca.addEventListener('change', function () {
            const archivo = this.files[0];
            if (archivo) {
                const lector = new FileReader();
                lector.onload = function (e) {
                    previewFotoMarca.src = e.target.result;
                    previewFotoMarca.style.display = 'block';
                    iconoMarca.style.opacity = '0';
                };
                lector.readAsDataURL(archivo);
            }
        });
    }

    // Abrir modal
    const btnNuevaMarca = document.querySelector('[data-modal-target="new_marca"]');
    const modalMarca = document.querySelector('[data-modal="new_marca"]');

    if (btnNuevaMarca && modalMarca) {
        btnNuevaMarca.addEventListener('click', () => {
            if (modalMarca.showModal) modalMarca.showModal();
            limpiarFormularioMarca(formMarca);
        });
    }

    // Envío del formulario
    if (formMarca) {
        formMarca.addEventListener('submit', function (e) {
            e.preventDefault();

            const marcaId = document.getElementById('marca_id').value;
            const formData = new FormData(formMarca);
            const accion = marcaId ? 'actualizar' : 'crear';

            formData.append('accion', accion);
            if (marcaId) formData.append('marca_id', marcaId);

            fetch('php/marca_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                errorContainerMarca.innerHTML = '';
                errorContainerMarca.style.display = 'none';

                if (data.error) {
                    errorContainerMarca.innerHTML = `<p>${data.mensaje}</p>`;
                    errorContainerMarca.style.display = 'block';

                    if (data.campos && Array.isArray(data.campos)) {
                        data.campos.forEach((campo, index) => {
                            const input = formMarca.querySelector(`[name="${campo}"]`);
                            if (input) {
                                input.classList.add('input-error');
                                if (index === 0) input.focus();
                            }
                        });
                    }
                } else if (data.exito) {
                    const mensaje = marcaId ? "Marca actualizada con éxito" : "Marca registrada con éxito";
                    modalMarca?.close();
                    mostrarModalExito(mensaje);
                    limpiarFormularioMarca(formMarca);
                    $('#marcaTabla').DataTable().ajax.reload(null, false);
                }
            })
            .catch(() => {
                errorContainerMarca.innerHTML = 'Hubo un error con el servidor';
                errorContainerMarca.style.display = 'block';
            });
        });
    }
});


// Mostrar información de marca
function mostrarInfoMarca(data) {
    document.getElementById('info_codigo_marca').textContent = data.marca_codigo || '';
    document.getElementById('info_nombre_marca').textContent = data.marca_nombre || '';
    document.getElementById('marca_imagen_info_marca').src = data.marca_imagen?.trim() !== '' ? data.marca_imagen : '';

    const modal = document.querySelector('dialog[data-modal="info_marca"]');
    if (modal?.showModal) modal.showModal();
}

// Limpiar modal info
document.querySelector('dialog[data-modal="info_marca"] .modal__close')?.addEventListener('click', function () {
    document.querySelector('dialog[data-modal="info_marca"]')?.close();
});

// Mostrar datos en confirmación
function mostrarConfirmacionMarca(data, modo = 'eliminar') {
    const imgId = modo === 'eliminar' ? 'delete_imagen_marca' : 'confirmar_imagen_marca';
    const img = document.getElementById(imgId);
    img.src = data.marca_imagen?.trim() !== '' ? data.marca_imagen + '?t=' + new Date().getTime() : '';

    const codigoId = modo === 'eliminar' ? 'delete_codigo_marca' : 'confirmar_codigo_marca';
    const nombreId = modo === 'eliminar' ? 'delete_nombre_marca' : 'confirmar_nombre_marca';

    document.getElementById(codigoId).textContent = data.marca_codigo || '';
    document.getElementById(nombreId).textContent = data.marca_nombre || '';

    const formId = modo === 'eliminar' ? 'form_delete_marca' : 'form_confirmar_marca';
    const modalId = modo === 'eliminar' ? 'eliminar_marca' : 'confirmar_marca';

    const form = document.getElementById(formId);
    form.dataset.marcaId = data.marca_id;
    form.dataset.modo = modo;

    document.querySelector(`dialog[data-modal="${modalId}"]`)?.showModal();
}

// Eliminar marca
document.getElementById('form_delete_marca')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.dataset.marcaId;
    if (!id) return;

    fetch('php/marca_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'deshabilitar_marca', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            document.querySelector('dialog[data-modal="eliminar_marca"]')?.close();
            mostrarModalExito(data.mensaje || 'Marca deshabilitada');
            $('#marcaTabla').DataTable().ajax.reload(null, false);
        }
    });
});

// Recuperar marca
document.getElementById('form_confirmar_marca')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.dataset.marcaId;
    if (!id) return;

    fetch('php/marca_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'recuperar_marca', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            document.querySelector('dialog[data-modal="confirmar_marca"]')?.close();
            mostrarModalExito(data.mensaje || 'Marca recuperada');
            estadoActual = 1;
            $('#marcaTabla').DataTable().ajax.reload(null, false);
        }
    });
});


/*MODULO BIENES_TIPO*/

// Función: formulario de actualizar
function abrirFormularioEdicionBien(id) {
    const formBien = document.getElementById('form_nuevo_bien');
    if (!formBien) return;

    fetch('php/bien_tipo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_bien', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.bien) return;

        const b = data.bien;
        formBien.querySelector('#bien_tipo_id').value = b.bien_tipo_id;
        formBien.querySelector('#codigo').value = b.bien_codigo;
        formBien.querySelector('#nombre_bien').value = b.bien_nombre;
        formBien.querySelector('#modelo').value = b.bien_modelo;
        formBien.querySelector('#marca').value = b.marca_id;
        formBien.querySelector('#clasificacion').value = b.clasificacion_id;
        formBien.querySelector('#descripcion').value = b.bien_descripcion;

        const preview = document.getElementById('preview_foto');
        const icono = document.querySelector('.foto_perfil_icon');

        if (b.bien_imagen && b.bien_imagen.trim() !== '') {
            preview.src = b.bien_imagen + '?t=' + new Date().getTime();
            preview.style.display = 'block';
            icono.style.opacity = '0';
        } else {
            preview.src = '';
            preview.style.display = 'none';
            icono.style.opacity = '1';
        }

        document.querySelector('[data-modal="new_bien_tipo"]')?.showModal();
    })
    .catch(err => console.error('Error al obtener bien:', err));
}

// Crear o actualizar
document.addEventListener('DOMContentLoaded', function () {
    const formBien = document.getElementById('form_nuevo_bien');
    const inputFotoBien = document.getElementById('foto_bien');
    const previewFotoBien = document.getElementById('preview_foto');
    const iconoBien = formBien?.querySelector('.foto_perfil_icon');

    // Previsualizar imagen de bien
    if (inputFotoBien && previewFotoBien && iconoBien) {
        inputFotoBien.addEventListener('change', function () {
            const archivo = this.files[0];
            if (archivo) {
                const lector = new FileReader();
                lector.onload = function (e) {
                    previewFotoBien.src = e.target.result;
                    previewFotoBien.style.display = 'block';
                    iconoBien.style.opacity = '0';
                };
                lector.readAsDataURL(archivo);
            }
        });
    }


    // Abrir modal
    const btnNuevo = document.querySelector('[data-modal-target="new_bien_tipo"]');
    if (btnNuevo) {
        btnNuevo.addEventListener('click', () => {
            const modal = document.querySelector('[data-modal="new_bien_tipo"]');
            limpiarFormularioBien(formBien);
            if (modal?.showModal) modal.showModal();
            cargarCategorias();
            cargarClasificacion();
            cargarMarca();
        });
    }

    // Envío del formulario
    if (formBien) {
        formBien.addEventListener('submit', function (e) {
            e.preventDefault();

            const bienTipoId = document.getElementById('bien_tipo_id').value;
            const formData = new FormData(formBien);
            const accion = bienTipoId ? 'actualizar' : 'crear';

            formData.append('accion', accion);
            if (bienTipoId) formData.append('bien_tipo_id', bienTipoId);

            fetch('php/bien_tipo_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                errorContainer.innerHTML = '';
                errorContainer.style.display = 'none';

                if (data.error) {
                    errorContainer.innerHTML = `<p>${data.mensaje}</p>`;
                    errorContainer.style.display = 'block';

                    if (data.campos && Array.isArray(data.campos)) {
                        data.campos.forEach((campo, index) => {
                            const input = formBien.querySelector(`[name="${campo}"]`);
                            if (input) {
                                input.classList.add('input-error');
                                if (index === 0) input.focus();
                            }
                        });
                    }
                } else if (data.exito) {
                    const mensaje = bienTipoId ? "Bien actualizado con éxito" : "Bien registrado con éxito";
                    document.querySelector('dialog[data-modal="new_bien_tipo"]')?.close();
                    mostrarModalExito(mensaje);
                    limpiarFormularioBien(formBien);
                    $('#bienTipoTabla').DataTable().ajax.reload(null, false);
                }
            })
            .catch(() => {
                errorContainer.innerHTML = 'Hubo un error con el servidor';
                errorContainer.style.display = 'block';
            });
        });
    }
});



// Mostrar información
function mostrarInfoBien(data) {
    document.getElementById('info_codigo').textContent = data.bien_codigo || '';
    document.getElementById('info_nombre').textContent = data.bien_nombre || '';
    document.getElementById('info_modelo').textContent = data.bien_modelo || '';
    document.getElementById('info_marca').textContent = data.marca_nombre || '';
    document.getElementById('info_clasificacion').textContent = data.clasificacion_nombre || '';
    document.getElementById('info_descripcion').textContent = data.bien_descripcion || '';
    document.getElementById('info_imagen').src = data.bien_imagen?.trim() !== '' ? data.bien_imagen : '';

    const modal = document.querySelector('dialog[data-modal="info_bien"]');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
}


// Mostrar datos en confirmación
function mostrarConfirmacionBien(data, modo = 'eliminar') {
    if (modo === 'eliminar') {
        document.getElementById('delete_codigo_bien').textContent = data.bien_codigo || '';
        document.getElementById('delete_nombre_bien').textContent = data.bien_nombre || '';
        document.getElementById('delete_clasificacion_bien').textContent = data.clasificacion_nombre || '';
        document.getElementById('delete_imagen_bien').src = data.bien_imagen?.trim() !== '' ? data.bien_imagen + '?t=' + new Date().getTime() : '';

        const formBien = document.getElementById('form_delete_bien');
        formBien.dataset.bienTipoId = data.bien_tipo_id;
        formBien.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="eliminar_bien"]');
        if (modal?.showModal) modal.showModal();
    } else if (modo === 'recuperar') {
        document.getElementById('confirmar_codigo_bien').textContent = data.bien_codigo || '';
        document.getElementById('confirmar_nombre_bien').textContent = data.bien_nombre || '';
        document.getElementById('confirmar_clasificacion_bien').textContent = data.clasificacion_nombre || '';
        document.getElementById('confirmar_imagen_bien').src = data.bien_imagen?.trim() !== '' ? data.bien_imagen + '?t=' + new Date().getTime() : '';

        const formBien = document.getElementById('form_confirmar_bien');
        formBien.dataset.bienTipoId = data.bien_tipo_id;
        formBien.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="confirmar_bien"]');
        if (modal?.showModal) modal.showModal();
    }
}



// Eliminar
document.getElementById('form_delete_bien').addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.bienTipoId;
    if (!id) return;

    fetch('php/bien_tipo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'deshabilitar_bien', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="eliminar_bien"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Bien deshabilitado');
            $('#bienTipoTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});


document.getElementById('form_confirmar_bien').addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.bienTipoId;
    if (!id) return;

    fetch('php/bien_tipo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'recuperar_bien', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="confirmar_bien"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Bien recuperado');
            estadoActual = 1;
            $('#bienTipoTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});

document.addEventListener('change', function (e) {
    if (e.target.matches('#categoria_filtro') || e.target.matches('#clasificacion_filtro')) {
        $('#bienTipoTabla').DataTable().ajax.reload(null, false);
    }
});

