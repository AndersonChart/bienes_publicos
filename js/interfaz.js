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
                document.getElementById('preview_foto').src = u.usuario_foto || 'img/icons/perfil.png';

                const modal = document.querySelector('dialog[data-modal="new_user"]');
                if (modal?.showModal) modal.showModal();
            }
        });
    });
}

/**
 * Carga categorías desde el backend y las inserta en un elemento select o contenedor.
 * @param {string} selector - Selector CSS del elemento destino (ej: '#categoria', '.filtro-categorias')
 * @param {object} opciones - Opciones adicionales:
 *   - includeDefault: boolean (agrega opción "Seleccione una categoría")
 *   - onComplete: function (callback con los datos)
 */
function cargarCategorias(selector, opciones = {}) {
    fetch('php/categoria_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'leer_todas' })
    })
    .then(res => res.json())
    .then(data => {
        const contenedor = document.querySelector(selector);
        if (!contenedor) return;

        // Si es un <select>
        if (contenedor.tagName === 'SELECT') {
            contenedor.innerHTML = '';
            if (opciones.includeDefault) {
                const defaultOption = document.createElement('option');
                defaultOption.value = 'Seleccione';
                defaultOption.disabled = true;
                defaultOption.selected = true;
                defaultOption.textContent = '';
                contenedor.appendChild(defaultOption);
            }

            data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.categoria_id;
                option.textContent = cat.categoria_nombre;
                contenedor.appendChild(option);
            });
        }

        if (typeof opciones.onComplete === 'function') {
            opciones.onComplete(data);
        }
    })
    .catch(() => {
        console.error('Error al cargar categorías');
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

});

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


//Crear usuario
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
            const esActualizacion = !!document.getElementById('usuario_id').value;
            const mensaje = esActualizacion ? "Usuario actualizado con éxito" : "Usuario registrado con éxito";
            

            // Solo cerrar el modal si fue una actualización
            if (esActualizacion) {
                const modalFormulario = document.querySelector('dialog[data-modal="new_user"]');
                if (modalFormulario && modalFormulario.open) {
                    modalFormulario.close();
                }
            }

            mostrarModalExito(mensaje);

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

//Mostrar datos en confirmacion
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



//Eliminar/recuperar Usuario
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

    const cargar = new Promise(resolve => {
        cargarCategorias('#categoria_form', {
            includeDefault: true,
            onComplete: resolve
        });
    });

    const obtener = fetch('php/clasificacion_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_clasificacion', id })
    }).then(res => res.json());

    Promise.all([cargar, obtener]).then(([_, data]) => {
        if (!data.exito || !data.clasificacion) return;

        const c = data.clasificacion;
        form.clasificacion_id.value = c.clasificacion_id;
        form.codigo.value = c.clasificacion_codigo;
        form.nombre.value = c.clasificacion_nombre;
        form.descripcion.value = c.clasificacion_descripcion;
        form.querySelector('[name="categoria_id"]').value = c.categoria_id;

        const modal = document.querySelector('[data-modal="new_clasificacion"]');
        if (modal) modal.showModal();
    });
}



// Crear o actualizar clasificación
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form_nueva_clasificacion');
    const errorContainer = document.getElementById('error-container-clasificacion');

    //Seleccion de categoria
    document.querySelector('[data-modal-target="new_clasificacion"]')?.addEventListener('click', () => {
    const modal = document.querySelector('[data-modal="new_clasificacion"]');
    if (modal?.showModal) modal.showModal();

    // Esperar a que el modal esté visible antes de cargar categorías
    setTimeout(() => {
        cargarCategorias('#categoria_form', { includeDefault: true });
    }, 100); // pequeño delay para asegurar que el DOM esté listo
});



    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const clasificacionId = document.getElementById('clasificacion_id').value;
        const formData = new FormData(form);

        // Acción condicional
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

                if (esActualizacion) {
                    const modalFormulario = document.querySelector('dialog[data-modal="new_clasificacion"]');
                    if (modalFormulario && modalFormulario.open) {
                        modalFormulario.close();
                    }
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
});

// Mostrar información de clasificación
function mostrarInfoclasificacion(data) {
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
