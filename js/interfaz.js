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

//Función global: mostrar errores
function mostrarError(containerId, mensaje) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = `<p>${mensaje}</p>`;
    el.style.display = 'block';
}

function limpiarError(containerId) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = '';
    el.style.display = 'none';
}

// Función global: asignar fecha de hoy a un input date
function asignarFechaHoy(inputDate) {
    if (!inputDate) return;
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    const fechaHoy = `${yyyy}-${mm}-${dd}`;
    inputDate.value = fechaHoy;
    inputDate.setAttribute('max', fechaHoy);
}

// Función global: validar fecha (no vacía, formato correcto, no futura)
function validarFecha(fecha) {
    if (!fecha) return 'Debe rellenar la fecha del ajuste';
    const regexFecha = /^\d{4}-\d{2}-\d{2}$/;
    if (!regexFecha.test(fecha)) return 'La fecha debe tener formato YYYY-MM-DD';

    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    const fechaHoy = `${yyyy}-${mm}-${dd}`;

    if (fecha > fechaHoy) return 'La fecha no puede ser posterior al día de hoy';
    return null;
}



//Editar perfil de usuario al presionar a su foto de perfil
function activarEdicionPerfil() {
    const btn = document.getElementById('btn_editar_perfil');
    const modal = document.querySelector('dialog[data-modal="user_edit"]');
    const btnCerrar = document.getElementById('cerrar_modal_user_edit');
    const form = document.getElementById('form_editar_perfil');
    const errorContainer = document.getElementById('error-container-perfil');
    const inputFoto = document.getElementById('foto_editar_perfil');
    const previewFoto = document.getElementById('preview_foto_editar_perfil');
    const icono = document.querySelector('.foto_editar_perfil_icon');

    if (!btn || !modal || !btnCerrar || !form || !inputFoto || !previewFoto || !errorContainer) return;

    // Previsualizar nueva imagen seleccionada
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

    // Abrir modal y cargar datos del usuario en sesión
    btn.addEventListener('click', () => {
        fetch('php/usuario_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_usuario', id: idUsuarioSesion })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.usuario) {
                const u = data.usuario;
                document.getElementById('usuario_id_perfil').value = u.usuario_id;
                document.getElementById('nombre_perfil').value = u.usuario_nombre;
                document.getElementById('apellido_perfil').value = u.usuario_apellido;
                document.getElementById('correo_perfil').value = u.usuario_correo;
                document.getElementById('telefono_perfil').value = u.usuario_telefono;
                document.getElementById('tipo_cedula_perfil').value = u.usuario_cedula.charAt(0);
                document.getElementById('numero_cedula_perfil').value = u.usuario_cedula.slice(2);
                document.getElementById('sexo_perfil').value = u.usuario_sexo;
                document.getElementById('direccion_perfil').value = u.usuario_direccion;
                document.getElementById('nac_perfil').value = u.usuario_nac;
                document.getElementById('nombre_usuario_perfil').value = u.usuario_usuario;

                // Mostrar foto actual en el preview
                const foto = u.usuario_foto && u.usuario_foto.trim() !== '' 
                    ? u.usuario_foto 
                    : 'img/icons/perfil.png';
                previewFoto.src = foto + '?t=' + new Date().getTime();
                previewFoto.style.display = 'block';
                icono.style.opacity = '0';

                // Actualizar avatar del header
                const avatarHeader = document.getElementById('foto_usuario_header');
                if (avatarHeader) avatarHeader.src = foto;

                errorContainer.innerHTML = '';
                errorContainer.style.display = 'none';
                modal.showModal();
            }
        });
    });

    // Cerrar modal
    btnCerrar.addEventListener('click', () => {
        modal.close();
    });

    // Enviar formulario
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const tipo = document.getElementById('tipo_cedula_perfil').value;
        const numero = document.getElementById('numero_cedula_perfil').value.trim();
        const cedulaCompleta = tipo + '-' + numero;

        const formData = new FormData(form);
        formData.set('usuario_cedula', cedulaCompleta);
        formData.append('accion', 'actualizar');
        formData.append('usuario_id', document.getElementById('usuario_id_perfil').value);

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
                // marcar campos con error...
            } else if (data.exito) {
                modal.close();
                form.reset();
                previewFoto.src = 'img/icons/perfil.png';
                previewFoto.style.display = 'none';
                icono.style.opacity = '1';

                const successModal = document.querySelector('dialog[data-modal="success"]');
                document.getElementById('success-message').textContent = "Perfil actualizado con éxito";
                if (successModal?.showModal) successModal.showModal();

                //  Recargar banner con datos actualizados
                fetch('php/usuario_ajax.php', {
                    method: 'POST',
                    body: new URLSearchParams({ accion: 'obtener_usuario', id: idUsuarioSesion })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.exito && data.usuario) {
                        const u = data.usuario;

                        // Actualizar nombre y apellido
                        const usernameBanner = document.querySelector('.inicio-username');
                        if (usernameBanner) {
                            usernameBanner.textContent = u.usuario_nombre + " " + u.usuario_apellido;
                        }

                        // Actualizar rol
                        const rolBanner = document.querySelector('.inicio-rol');
                        if (rolBanner) {
                            rolBanner.textContent = u.nombre_rol;
                        }

                        // Actualizar foto
                        const avatarHeader = document.getElementById('foto_usuario_header');
                        if (avatarHeader) {
                            const nuevaFoto = u.usuario_foto || 'img/icons/perfil.png';
                            avatarHeader.src = nuevaFoto + '?t=' + new Date().getTime();
                        }
                    }
                });
            }
        })
        .catch(() => {
            errorContainer.innerHTML = 'Hubo un error con el servidor';
            errorContainer.style.display = 'block';
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

function cargarRol(opciones = {}) {
    fetch('php/rol_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'leer_todos' })
    })
    .then(res => res.json())
    .then(data => {
        if (!Array.isArray(data)) {
            console.error('Respuesta inválida al cargar roles:', data);
            return;
        }

        document.querySelectorAll('select.rol_form').forEach(select => {
            select.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.disabled = true;
            defaultOption.textContent = 'Seleccione un rol';

            // Solo marcar como selected si NO hay rol preseleccionado (es creación)
            if (!opciones.selected) {
                defaultOption.selected = true;
            }

            select.appendChild(defaultOption);

            data.forEach(rol => {
                const option = document.createElement('option');
                option.value = rol.rol_id;
                option.textContent = rol.rol_nombre;

                // Marcar como seleccionado si estamos actualizando
                if (opciones.selected && opciones.selected == rol.rol_id) {
                    option.selected = true;
                }

                select.appendChild(option);
            });
        });

        if (typeof opciones.onComplete === 'function') {
            opciones.onComplete(data);
        }
    })
    .catch(err => {
        console.error('Error al cargar roles:', err);
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

function cargarCategorias(opciones = {}) {
    fetch('php/categoria_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'leer_todas' })
    })
    .then(res => res.json())
    .then(resp => {
        const data = resp.data;

        if (!Array.isArray(data)) {
            console.error('Respuesta inválida al cargar categorías:', resp);
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

            if (opciones.selected && opciones.scope === 'filtro') {
                select.value = opciones.selected;
            }
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
                option.value = String(cat.categoria_id);
                option.textContent = cat.categoria_nombre;
                select.appendChild(option);
            });

            if (opciones.selected && opciones.scope === 'form') {
                select.value = opciones.selected;
            }
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

    if (opciones.categoria_id) {
        params.append('categoria_id', opciones.categoria_id);
    }

    fetch('php/clasificacion_ajax.php', {
        method: 'POST',
        body: params
    })
    .then(res => res.json())
    .then(resp => {
        const lista = Array.isArray(resp.data) ? resp.data : [];

        // Selects para filtros
        document.querySelectorAll('select.clasificacion_filtro').forEach(select => {
            if (lista.length > 0) {
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

                if (opciones.selected && opciones.scope === 'filtro') {
                    select.value = opciones.selected;
                }
            } else {
                // No limpiar si no hay resultados
                console.warn('No se devolvieron clasificaciones para la categoría', opciones.categoria_id);
            }
        });

        // Selects para formularios
        document.querySelectorAll('select.clasificacion_form').forEach(select => {
            if (lista.length > 0) {
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

                if (opciones.selected && opciones.scope === 'form') {
                    select.value = opciones.selected;
                }
            } else {
                console.warn('No se devolvieron clasificaciones para la categoría', opciones.categoria_id);
            }
        });

        if (typeof opciones.onComplete === 'function') {
            opciones.onComplete(lista);
        }
    })
    .catch(err => {
        console.error('Error al cargar clasificación:', err);
    });
}

function cargarCargo(opciones = {}) {
    fetch('php/cargo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'leer_todos' })
    })
    .then(res => res.json())
    .then(resp => {
        const data = resp.data;

        if (!Array.isArray(data)) {
            console.error('Respuesta inválida al cargar cargos:', resp);
            return;
        }

        // Selects para filtros
        document.querySelectorAll('select.cargo_filtro').forEach(select => {
            select.innerHTML = '';
            const todasOption = document.createElement('option');
            todasOption.value = '';
            todasOption.textContent = 'Todos los cargos';
            select.appendChild(todasOption);

            data.forEach(cargo => {
                const option = document.createElement('option');
                option.value = cargo.cargo_id;
                option.textContent = cargo.cargo_nombre;
                select.appendChild(option);
            });

            if (opciones.selected && opciones.scope === 'filtro') {
                select.value = opciones.selected;
            }
        });

        // Selects para formularios
        document.querySelectorAll('select.cargo_form').forEach(select => {
            select.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.disabled = true;
            defaultOption.selected = true;
            defaultOption.textContent = 'Seleccione un cargo';
            select.appendChild(defaultOption);

            data.forEach(cargo => {
                const option = document.createElement('option');
                option.value = String(cargo.cargo_id);
                option.textContent = cargo.cargo_nombre;
                select.appendChild(option);
            });

            if (opciones.selected && opciones.scope === 'form') {
                select.value = opciones.selected;
            }
        });

        if (typeof opciones.onComplete === 'function') {
            opciones.onComplete(data);
        }
    })
    .catch(err => {
        console.error('Error al cargar cargos:', err);
    });
}


//Dinamicas de Formularios

//Articulo

// Función global: aplicar dinámica según categoria_tipo (0 = Básico, 1 = Completo)
function aplicarDinamicaCategoria() {
    const formArticulo = document.getElementById('form_nuevo_articulo');
    if (!formArticulo) return;

    const inputModelo = formArticulo.querySelector('[name="articulo_modelo"]');
    const selectMarca = formArticulo.querySelector('[name="marca_id"]');

    // Normalizar el tipo: solo aceptar "0" o "1"
    const rawTipo = (formArticulo.dataset.categoriaTipo ?? '').trim();
    const tipo = (rawTipo === '0' || rawTipo === '1') ? rawTipo : '';

    const deshabilitar = (el) => {
        if (!el) return;
        el.disabled = true;
        el.classList.add('is-disabled');
        el.style.opacity = '0.5';
    };
    const habilitar = (el) => {
        if (!el) return;
        el.disabled = false;
        el.classList.remove('is-disabled');
        el.style.opacity = '1';
    };
    const borrarValor = (el) => {
        if (!el) return;
        el.value = '';
    };

    if (tipo === '0') {
        // Básico: modelo y marca no aplican
        if (inputModelo) {
            borrarValor(inputModelo);
            deshabilitar(inputModelo);
        }
        if (selectMarca) {
            borrarValor(selectMarca);
            deshabilitar(selectMarca);
            selectMarca.dispatchEvent(new Event('change'));
        }
    } else if (tipo === '1') {
        // Completo: ambos habilitados
        if (inputModelo) {
            habilitar(inputModelo);
        }
        if (selectMarca) {
            habilitar(selectMarca);
            selectMarca.dispatchEvent(new Event('change'));
        }
    } else {
        // Sin tipo (no hay clasificación válida): habilitar sin requerir
        if (inputModelo) {
            habilitar(inputModelo);
        }
        if (selectMarca) {
            habilitar(selectMarca);
            selectMarca.dispatchEvent(new Event('change'));
        }
    }
}


// Función global: Limpiar formularios
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

    // Reiniciar selects a su opción por defecto ("Seleccione...")
    form.querySelectorAll('select').forEach(select => {
        const defaultOption = select.querySelector('option[disabled][selected]');
        if (defaultOption) {
            select.value = defaultOption.value; // vuelve al "Seleccione..."
        } else {
            select.selectedIndex = 0; // fallback: primer opción
        }
    });

    // Variable exclusiva para el campo de fecha
    const fechaInput = form.querySelector('#ajuste_fecha');
    if (fechaInput) {
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        const fechaHoy = `${yyyy}-${mm}-${dd}`;

        fechaInput.value = fechaHoy;       // siempre asigna la fecha actual
        fechaInput.setAttribute('max', fechaHoy); // bloquea futuras
        console.log("Fecha asignada automáticamente:", fechaHoy);
    }
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
    cargarRol();
    cargarClasificacion();
    cargarMarca();
    cargarCargo();
    mantenerFotoUsuarioActualizada();
    aplicarDinamicaCategoria()

    // Función para ajustar columnas según tipo de categoría
        function ajustarColumnasPorCategoria(categoriaId) {
            const tabla = $('#articuloTabla').DataTable();

            // Busca el tipo de categoría en los datos actuales
            const data = tabla.rows({ page: 'current' }).data();
            if (data.length > 0) {
                // Si todas son básicas (categoria_tipo = 0)
                const todasBasicas = data.every(row => parseInt(row.categoria_tipo, 10) === 0);
                if (todasBasicas) {
                    tabla.column(4).visible(false); // Modelo
                    tabla.column(5).visible(false); // Marca
                    return;
                }

                // Si todas son completas (categoria_tipo = 1)
                const todasCompletas = data.every(row => parseInt(row.categoria_tipo, 10) === 1);
                if (todasCompletas) {
                    tabla.column(4).visible(true);
                    tabla.column(5).visible(true);
                    return;
                }

                // Mezcla: mostrar columnas pero dejar celdas vacías si no aplica
                tabla.column(4).visible(true);
                tabla.column(5).visible(true);
            }
        }

    // --- Filtros de inventario ---
    const filtroCategoria = document.getElementById('categoria_filtro');
    const filtroClasificacion = document.getElementById('clasificacion_filtro');



        if (filtroCategoria) {
    filtroCategoria.addEventListener('change', () => {
        const valor = filtroCategoria.value;

        if (valor !== '') {
            // Clasificaciones de una categoría específica
            cargarClasificacion({
                categoria_id: valor,
                scope: 'filtro',
                onComplete: (lista) => {
                    if (Array.isArray(lista) && lista.length > 0) {
                        filtroClasificacion.innerHTML = '';
                        const todasOption = document.createElement('option');
                        todasOption.value = '';
                        todasOption.textContent = 'Todas las clasificaciones';
                        filtroClasificacion.appendChild(todasOption);

                        lista.forEach(clasificacion => {
                            const option = document.createElement('option');
                            option.value = clasificacion.clasificacion_id;
                            option.textContent = clasificacion.clasificacion_nombre;
                            filtroClasificacion.appendChild(option);
                        });
                    } else {
                        // Mantener las opciones previas si no hay resultados
                        console.warn('No se devolvieron clasificaciones para la categoría', valor);
                    }
                }
            });
        } else {
            // Todas las categorías → cargar todas las clasificaciones
            cargarClasificacion({
                scope: 'filtro',
                onComplete: (lista) => {
                    if (Array.isArray(lista) && lista.length > 0) {
                        filtroClasificacion.innerHTML = '';
                        const todasOption = document.createElement('option');
                        todasOption.value = '';
                        todasOption.textContent = 'Todas las clasificaciones';
                        filtroClasificacion.appendChild(todasOption);

                        lista.forEach(clasificacion => {
                            const option = document.createElement('option');
                            option.value = clasificacion.clasificacion_id;
                            option.textContent = clasificacion.clasificacion_nombre;
                            filtroClasificacion.appendChild(option);
                        });
                    }
                }
            });
        }

        // Recargar primero, ajustar columnas después
        $('#articuloTabla').DataTable().ajax.reload(() => {
            ajustarColumnasPorCategoria(valor);
        }, false);
    });
        }

        if (filtroClasificacion) {
            filtroClasificacion.addEventListener('change', () => {
                const clasificacionId = filtroClasificacion.value;
                const categoriaActual = filtroCategoria.value || 0;

                if (!clasificacionId) {
                    // Todas las clasificaciones → ajustar según la categoría actual
                    $('#articuloTabla').DataTable().ajax.reload(() => {
                        ajustarColumnasPorCategoria(categoriaActual);
                    }, false);
                    return;
                }

                fetch('php/clasificacion_ajax.php', {
                    method: 'POST',
                    body: new URLSearchParams({ accion: 'obtener_clasificacion', id: clasificacionId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.exito && data.clasificacion) {
                        filtroCategoria.value = data.clasificacion.categoria_id;
                    }
                    $('#articuloTabla').DataTable().ajax.reload(() => {
                        ajustarColumnasPorCategoria(
                            data.exito ? data.clasificacion.categoria_id : categoriaActual
                        );
                    }, false);
                });
            });
        }

        //FILTROS DE RECEPCION

        const filtroCategoriaRecepcion = document.getElementById('categoria_filtro');
        const filtroClasificacionRecepcion = document.getElementById('clasificacion_filtro');

        if (filtroCategoriaRecepcion) {
            filtroCategoriaRecepcion.addEventListener('change', () => {
                const valor = filtroCategoriaRecepcion.value;

                if (valor !== '') {
                    // Cargar clasificaciones de la categoría seleccionada
                    cargarClasificacion({
                        categoria_id: valor,
                        scope: 'filtro',
                        onComplete: (lista) => {
                            if (Array.isArray(lista) && lista.length > 0) {
                                filtroClasificacionRecepcion.innerHTML = '';
                                const todasOption = document.createElement('option');
                                todasOption.value = '';
                                todasOption.textContent = 'Todas las clasificaciones';
                                filtroClasificacionRecepcion.appendChild(todasOption);

                                lista.forEach(clasificacion => {
                                    const option = document.createElement('option');
                                    option.value = clasificacion.clasificacion_id;
                                    option.textContent = clasificacion.clasificacion_nombre;
                                    filtroClasificacionRecepcion.appendChild(option);
                                });
                            }
                        }
                    });
                } else {
                    // Todas las categorías → cargar todas las clasificaciones
                    cargarClasificacion({
                        scope: 'filtro',
                        onComplete: (lista) => {
                            if (Array.isArray(lista) && lista.length > 0) {
                                filtroClasificacionRecepcion.innerHTML = '';
                                const todasOption = document.createElement('option');
                                todasOption.value = '';
                                todasOption.textContent = 'Todas las clasificaciones';
                                filtroClasificacionRecepcion.appendChild(todasOption);

                                lista.forEach(clasificacion => {
                                    const option = document.createElement('option');
                                    option.value = clasificacion.clasificacion_id;
                                    option.textContent = clasificacion.clasificacion_nombre;
                                    filtroClasificacionRecepcion.appendChild(option);
                                });
                            }
                        }
                    });
                }

                // Recargar la tabla de recepción con el filtro aplicado
                $('#recepcionArticuloTabla').DataTable().ajax.reload(null, false);
            });
        }

        if (filtroClasificacionRecepcion) {
            filtroClasificacionRecepcion.addEventListener('change', () => {
                const clasificacionId = filtroClasificacionRecepcion.value;
                const categoriaActual = filtroCategoriaRecepcion.value || '';

                if (!clasificacionId) {
                    // Todas las clasificaciones → recargar según la categoría actual
                    $('#recepcionArticuloTabla').DataTable().ajax.reload(null, false);
                    return;
                }

                fetch('php/clasificacion_ajax.php', {
                    method: 'POST',
                    body: new URLSearchParams({ accion: 'obtener_clasificacion', id: clasificacionId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.exito && data.clasificacion) {
                        filtroCategoriaRecepcion.value = data.clasificacion.categoria_id;
                    }
                    $('#recepcionArticuloTabla').DataTable().ajax.reload(null, false);
                });
            });
        }

});


//Al cargar que pueda hacer las siguiente funciones
window.addEventListener('load', () => {
    activarEdicionPerfil();
});


/*MODULO USUARIO*/

//Función: formulario de actualizar usuario
function abrirFormularioEdicionUsuario(id) {
    const form = document.getElementById('form_nuevo_usuario');
    if (!form) return;

    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_usuario', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.usuario) return;

        const u = data.usuario;

        cargarRol({
            selected: u.rol_id,
            onComplete: () => {
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

                const previewFoto = document.getElementById('preview_foto');
                const icono = document.querySelector('.foto_perfil_icon');
                previewFoto.src = u.usuario_foto || 'img/icons/perfil.png';
                previewFoto.style.display = 'block';
                icono.style.opacity = '0';

                const modal = document.querySelector('[data-modal="new_user"]');
                if (modal?.showModal) modal.showModal();
            }
        });
    });
}

//Crear/actualizar usuario
document.addEventListener('DOMContentLoaded', function () {
    const inputFoto = document.getElementById('foto');
    const previewFoto = document.getElementById('preview_foto');
    const icono = document.querySelector('.foto_perfil_icon');
    const form = document.getElementById('form_nuevo_usuario');
    const errorContainer = document.getElementById('error-container-usuario');

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

        // Solo asignar rol si estás creando y el select está vacío
        if (!usuarioId && !formData.get('rol_id')) {
            formData.append('rol_id', '1'); // valor por defecto solo si no eligió nada
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
    document.getElementById('info_rol').textContent = data.rol_nombre || '';

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
    const imgId = modo === 'eliminar' ? 'delete_foto' : 'confirmar_foto';
    const img = document.getElementById(imgId);
    img.src = data.usuario_foto?.trim() !== '' ? data.usuario_foto + '?t=' + new Date().getTime() : 'img/icons/perfil.png';

    const usuarioId = modo === 'eliminar' ? 'delete_usuario' : 'confirmar_usuario';
    const nombreId = modo === 'eliminar' ? 'delete_nombre' : 'confirmar_nombre_completo';
    const apellidoId = modo === 'eliminar' ? 'delete_apellido' : null;

    document.getElementById(usuarioId).textContent = data.usuario_usuario || '';
    document.getElementById(nombreId).textContent = `${data.usuario_nombre || ''} ${data.usuario_apellido || ''}`.trim();
    if (apellidoId) {
        document.getElementById(apellidoId).textContent = data.usuario_apellido || '';
    }

    const formId = modo === 'eliminar' ? 'form_delete_usuario' : 'form_confirmar_usuario';
    const modalId = modo === 'eliminar' ? 'eliminar_usuario' : 'confirmar_usuario';

    const form = document.getElementById(formId);
    form.dataset.usuarioId = data.usuario_id;
    form.dataset.modo = modo;

    document.querySelector(`dialog[data-modal="${modalId}"]`)?.showModal();
}



//funcion: Eliminar
document.getElementById('form_delete_usuario')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.dataset.usuarioId;
    if (!id) return;

    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'deshabilitar_usuario', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            document.querySelector('dialog[data-modal="eliminar_usuario"]')?.close();
            mostrarModalExito(data.mensaje || 'Usuario deshabilitado');
            $('#usuarioTabla').DataTable().ajax.reload(null, false);
        }
    });
});

//Recuperar
document.getElementById('form_confirmar_usuario')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.dataset.usuarioId;
    if (!id) return;

    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'recuperar_usuario', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            document.querySelector('dialog[data-modal="confirmar_usuario"]')?.close();
            mostrarModalExito(data.mensaje || 'Usuario recuperado');
            estadoActual = 1;
            $('#usuarioTabla').DataTable().ajax.reload(null, false);
        }
    });
});

/* SUBMÓDULO CATEGORÍA */

// Función: abrir formulario de edición de categoría
function abrirFormularioEdicionCategoria(id) {
    const form = document.getElementById('form_nueva_categoria');
    if (!form) return;

    fetch('php/categoria_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_categoria', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.categoria) return;

        const c = data.categoria;

        form.categoria_id.value = c.categoria_id;
        form.categoria_codigo.value = c.categoria_codigo;
        form.categoria_nombre.value = c.categoria_nombre;
        form.categoria_descripcion.value = c.categoria_descripcion;
        form.categoria_tipo.value = c.categoria_tipo; // 0 o 1

        const modal = document.querySelector('[data-modal="new_categoria"]');
        if (modal?.showModal) modal.showModal();
    })
    .catch(err => {
        console.error('Error al obtener categoría:', err);
    });
}

// Crear o actualizar categoría
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form_nueva_categoria');
    const errorContainer = document.getElementById('error-container-categoria');
    const btnNuevo = document.querySelector('[data-modal-target="new_categoria"]');
    const modalFormulario = document.querySelector('dialog[data-modal="new_categoria"]');

    // Abrir modal y limpiar formulario
    if (btnNuevo && modalFormulario) {
        btnNuevo.addEventListener('click', () => {
            if (modalFormulario.showModal) modalFormulario.showModal();
            limpiarFormulario(form);
        });
    }

    // Envío del formulario
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const categoriaId = document.getElementById('categoria_id').value;
            const formData = new FormData(form);
            const accion = categoriaId ? 'actualizar' : 'crear';

            formData.append('accion', accion);
            if (categoriaId) formData.append('categoria_id', categoriaId);

            fetch('php/categoria_ajax.php', {
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
                    const esActualizacion = !!categoriaId;
                    const mensaje = esActualizacion
                        ? "Categoría actualizada con éxito"
                        : "Categoría registrada con éxito";

                    if (esActualizacion && modalFormulario && modalFormulario.open) {
                        modalFormulario.close();
                    }

                    mostrarModalExito(mensaje);
                    limpiarFormulario(form);
                    $('#categoriaTabla').DataTable().ajax.reload(null, false);
                }
            })
            .catch(() => {
                errorContainer.innerHTML = 'Hubo un error con el servidor';
                errorContainer.style.display = 'block';
            });
        });
    }
});

// Mostrar información de categoría
function mostrarInfoCategoria(data) {
    document.getElementById('info_codigo').textContent = data.categoria_codigo || '';
    document.getElementById('info_nombre').textContent = data.categoria_nombre || '';
    document.getElementById('info_descripcion').textContent = data.categoria_descripcion || '';
    document.getElementById('info_tipo').textContent = data.categoria_tipo == 1 ? 'Completo' : 'Básico';

    const modal = document.querySelector('dialog[data-modal="info_categoria"]');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
}

// Mostrar datos en confirmación
function mostrarConfirmacionCategoria(data, modo = 'eliminar') {
    if (modo === 'eliminar') {
        document.getElementById('delete_codigo').textContent = data.categoria_codigo || '';
        document.getElementById('delete_nombre').textContent = data.categoria_nombre || '';
        document.getElementById('delete_tipo').textContent = data.categoria_tipo == 1 ? 'Completo' : 'Básico';

        const form = document.getElementById('form_delete_categoria');
        form.dataset.categoriaId = data.categoria_id;
        form.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="eliminar_categoria"]');
        if (modal?.showModal) modal.showModal();
    } else if (modo === 'recuperar') {
        document.getElementById('confirmar_codigo').textContent = data.categoria_codigo || '';
        document.getElementById('confirmar_nombre').textContent = data.categoria_nombre || '';
        document.getElementById('confirmar_tipo').textContent = data.categoria_tipo == 1 ? 'Completo' : 'Básico';

        const form = document.getElementById('form_confirmar_categoria');
        form.dataset.categoriaId = data.categoria_id;
        form.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="confirmar_categoria"]');
        if (modal?.showModal) modal.showModal();
    }
}

// Eliminar categoría
document.getElementById('form_delete_categoria')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.categoriaId;
    if (!id) return;

    fetch('php/categoria_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'deshabilitar_categoria', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="eliminar_categoria"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Categoría deshabilitada');
            $('#categoriaTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});

// Recuperar categoría
document.getElementById('form_confirmar_categoria')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.categoriaId;
    if (!id) return;

    fetch('php/categoria_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'recuperar_categoria', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="confirmar_categoria"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Categoría recuperada');
            estadoActual = 1;
            $('#categoriaTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});

// Filtro de tipo de categoría
document.addEventListener('change', function (e) {
    if (e.target.matches('#categoria_tipo_filtro')) {
        $('#categoriaTabla').DataTable().ajax.reload(null, false);
    }
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

                    // Solo cerrar el modal si fue una actualización
                    if (esActualizacion && modalFormulario && modalFormulario.open) {
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
                    const esActualizacion = !!marcaId;
                    const mensaje = esActualizacion
                        ? "Marca actualizada con éxito"
                        : "Marca registrada con éxito";

                    // Solo cerrar el modal si fue una actualización
                    if (esActualizacion && modalMarca && modalMarca.open) {
                        modalMarca.close();
                    }

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




/*MODULO ARTICULOS*/

// Función: formulario de actualizar
function abrirFormularioEdicionArticulo(id) {
    const formArticulo = document.getElementById('form_nuevo_articulo');
    if (!formArticulo) return;

    fetch('php/articulo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_articulo', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.articulo) return;

        const a = data.articulo;
        console.log('Artículo recibido:', a); // Depuración

        // Cargar categorías primero
        cargarCategorias({
            onComplete: () => {
                // Marcar categoría en el formulario
                const categoriaSelect = document.getElementById('categoria_form-articulo');
                if (categoriaSelect) categoriaSelect.value = String(a.categoria_id ?? '');

                // Repoblar clasificaciones SOLO en el formulario
                cargarClasificacion({
                    categoria_id: a.categoria_id,
                    selected: a.clasificacion_id,
                    scope: 'form',
                    onComplete: () => {
                        const clasificacionSelect = document.getElementById('clasificacion_form-articulo');
                        if (clasificacionSelect) clasificacionSelect.value = String(a.clasificacion_id);

                        // Repoblar marcas SOLO en el formulario
                        cargarMarca({
                            selected: a.marca_id,
                            onComplete: () => {
                                const marcaSelect = document.getElementById('marca_form-articulo');
                                if (marcaSelect) marcaSelect.value = String(a.marca_id ?? '');

                                // Campos base
                                formArticulo.querySelector('#articulo_id').value = a.articulo_id;
                                formArticulo.querySelector('#articulo_codigo').value = a.articulo_codigo ?? '';
                                formArticulo.querySelector('#articulo_nombre').value = a.articulo_nombre ?? '';
                                formArticulo.querySelector('#articulo_modelo').value = a.articulo_modelo ?? '';
                                formArticulo.querySelector('#articulo_descripcion').value = a.articulo_descripcion ?? '';

                                // Imagen
                                const preview = document.getElementById('preview_foto_articulo');
                                const icono = document.querySelector('.foto_perfil_icon');
                                preview.src = (a.articulo_imagen && a.articulo_imagen.trim() !== '')
                                    ? a.articulo_imagen + '?t=' + Date.now()
                                    : 'img/icons/articulo.png';
                                preview.style.display = 'block';
                                if (icono) icono.style.opacity = '0';

                                // Dinámica desde backend
                                formArticulo.dataset.categoriaTipo = String(a.categoria_tipo ?? '');
                                aplicarDinamicaCategoria();

                                // Abrir modal
                                const modal = document.querySelector('[data-modal="new_articulo"]');
                                if (modal?.showModal) modal.showModal();
                            }
                        });
                    }
                });
            }
        });
    })
    .catch(err => console.error('Error al obtener artículo:', err));
}

// Crear o actualizar Artículo
document.addEventListener('DOMContentLoaded', function () {
    const formArticulo = document.getElementById('form_nuevo_articulo');
    const errorContainerArticulo = document.getElementById('error-container-articulo');
    const inputFotoArticulo = document.getElementById('foto_articulo');
    const previewFotoArticulo = document.getElementById('preview_foto_articulo');
    const iconoArticulo = formArticulo?.querySelector('.foto_perfil_icon');

    const selectCategoria = document.getElementById('categoria_form-articulo');
    const selectClasificacion = document.getElementById('clasificacion_form-articulo');

    // Listener de categoría dentro del formulario
    if (selectCategoria) {
        selectCategoria.addEventListener('change', () => {
            const categoriaId = selectCategoria.value;

            // Repoblar clasificaciones hijas SOLO en el formulario
            cargarClasificacion({
                categoria_id: categoriaId,
                scope: 'form',
                onComplete: (lista) => {
                    if (selectClasificacion) {
                        selectClasificacion.innerHTML = '';
                        const opt = document.createElement('option');
                        opt.value = '';
                        opt.disabled = true;
                        opt.selected = true;
                        opt.textContent = 'Seleccione una clasificación';
                        selectClasificacion.appendChild(opt);

                        lista.forEach(cl => {
                            const op = document.createElement('option');
                            op.value = cl.clasificacion_id;
                            op.textContent = cl.clasificacion_nombre;
                            selectClasificacion.appendChild(op);
                        });
                    }
                }
            });

            // Consultar tipo de categoría para aplicar dinámica
            fetch('php/categoria_ajax.php', {
                method: 'POST',
                body: new URLSearchParams({ accion: 'obtener_categoria', id: categoriaId })
            })
            .then(res => res.json())
            .then(data => {
                formArticulo.dataset.categoriaTipo = data.exito && data.categoria
                    ? String(data.categoria.categoria_tipo ?? '')
                    : "";
                aplicarDinamicaCategoria();
            })
            .catch(() => {
                formArticulo.dataset.categoriaTipo = "";
                aplicarDinamicaCategoria();
            });
        });
    }

    // Listener de clasificación dentro del formulario
    if (selectClasificacion) {
        selectClasificacion.addEventListener('change', () => {
            const clasificacionId = selectClasificacion.value;
            if (!clasificacionId) return;

            fetch('php/clasificacion_ajax.php', {
                method: 'POST',
                body: new URLSearchParams({ accion: 'obtener_clasificacion', id: clasificacionId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.exito && data.clasificacion) {
                    const categoriaId = data.clasificacion.categoria_id;
                    if (selectCategoria) selectCategoria.value = String(categoriaId ?? '');

                    formArticulo.dataset.categoriaTipo = String(data.clasificacion.categoria_tipo ?? '');
                    aplicarDinamicaCategoria();
                }
            });
        });
    }

    // Previsualizar imagen
    if (inputFotoArticulo && previewFotoArticulo && iconoArticulo) {
        inputFotoArticulo.addEventListener('change', function () {
            const archivo = this.files[0];
            if (archivo) {
                const lector = new FileReader();
                lector.onload = function (e) {
                    previewFotoArticulo.src = e.target.result;
                    previewFotoArticulo.style.display = 'block';
                    iconoArticulo.style.opacity = '0';
                };
                lector.readAsDataURL(archivo);
            }
        });
    }

    // Abrir modal nuevo
    const btnNuevo = document.querySelector('[data-modal-target="new_articulo"]');
    if (btnNuevo) {
        btnNuevo.addEventListener('click', () => {
            const modal = document.querySelector('[data-modal="new_articulo"]');
            limpiarFormulario(formArticulo);
            if (modal?.showModal) modal.showModal();

            cargarCategorias();
            cargarClasificacion({ scope: 'form' });
            cargarMarca();

            formArticulo.dataset.categoriaTipo = "";
            aplicarDinamicaCategoria();
        });
    }

    // Envío del formulario
    if (formArticulo) {
        formArticulo.addEventListener('submit', function (e) {
            e.preventDefault();

            const articuloId = document.getElementById('articulo_id').value;
            const formData = new FormData(formArticulo);
            const accion = articuloId ? 'actualizar' : 'crear';

            formData.append('accion', accion);
            if (articuloId) formData.append('articulo_id', articuloId);

            // Eliminar campos no aplicables si la categoría es Básica
            if (formArticulo.dataset.categoriaTipo === "0") {
                formData.delete('articulo_modelo');
                formData.delete('marca_id');
            }

            // No enviar categoria_id al backend
            formData.delete('categoria_id');

            // Depuración opcional
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            fetch('php/articulo_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                errorContainerArticulo.innerHTML = '';
                errorContainerArticulo.style.display = 'none';

                if (data.error) {
                    errorContainerArticulo.innerHTML = `<p>${data.mensaje}</p>`;
                    errorContainerArticulo.style.display = 'block';

                    if (data.campos && Array.isArray(data.campos)) {
                        data.campos.forEach((campo, index) => {
                            const input = formArticulo.querySelector(`[name="${campo}"]`);
                            if (input) {
                                input.classList.add('input-error');
                                if (index === 0) input.focus();
                            }
                        });
                    }
                } else if (data.exito) {
                    const esActualizacion = !!articuloId;
                    const mensaje = esActualizacion
                        ? "Artículo actualizado con éxito"
                        : "Artículo registrado con éxito";

                    const modalArticulo = document.querySelector('dialog[data-modal="new_articulo"]');
                    if (esActualizacion && modalArticulo && modalArticulo.open) {
                        modalArticulo.close();
                    }

                    mostrarModalExito(mensaje);
                    limpiarFormulario(formArticulo);

                    $('#articuloTabla').DataTable().ajax.reload(null, false);
                }
            })
            .catch(err => {
                console.error('Error en fetch:', err);
                errorContainerArticulo.innerHTML = 'Hubo un error con el servidor';
                errorContainerArticulo.style.display = 'block';
            });
        });
    }
});

// Mostrar información detallada de un artículo
function mostrarInfoArticulo(data) {
    document.getElementById('info_codigo').textContent = data.articulo_codigo || '';
    document.getElementById('info_nombre').textContent = data.articulo_nombre || '';
    document.getElementById('info_categoria').textContent = data.categoria_nombre || '';
    document.getElementById('info_clasificacion').textContent = data.clasificacion_nombre || '';
    document.getElementById('info_descripcion').textContent = data.articulo_descripcion || '';
    document.getElementById('info_imagen').src = (data.articulo_imagen?.trim() !== '') ? data.articulo_imagen : 'img/icons/articulo.png';

    // Condicional para marca y modelo según categoria_tipo (0 = Básico, 1 = Completo)
    const liMarca = document.getElementById('li_info_marca');
    const liModelo = document.getElementById('li_info_modelo');
    const categoriaTipo = Number(data.categoria_tipo ?? 0);

    if (categoriaTipo === 0 || !data.marca_nombre || data.marca_nombre.trim() === '') {
        liMarca.style.display = 'none';
    } else {
        liMarca.style.display = 'list-item';
        document.getElementById('info_marca').textContent = data.marca_nombre;
    }

    if (categoriaTipo === 0 || !data.articulo_modelo || data.articulo_modelo.trim() === '') {
        liModelo.style.display = 'none';
    } else {
        liModelo.style.display = 'list-item';
        document.getElementById('info_modelo').textContent = data.articulo_modelo;
    }

    const modal = document.querySelector('dialog[data-modal="info_articulo"]');
    if (modal?.showModal) modal.showModal();
}

// Mostrar datos en confirmación (eliminar o recuperar)
function mostrarConfirmacionArticulo(data, modo = 'eliminar') {
    if (modo === 'eliminar') {
        document.getElementById('delete_codigo_articulo').textContent = data.articulo_codigo || '';
        document.getElementById('delete_nombre_articulo').textContent = data.articulo_nombre || '';
        document.getElementById('delete_categoria_articulo').textContent = data.categoria_nombre || '';
        document.getElementById('delete_clasificacion_articulo').textContent = data.clasificacion_nombre || '';
        document.getElementById('delete_imagen_articulo').src = (data.articulo_imagen?.trim() !== '') 
            ? data.articulo_imagen + '?t=' + Date.now() 
            : 'img/icons/articulo.png';

        const formArticulo = document.getElementById('form_delete_articulo');
        formArticulo.dataset.articuloId = data.articulo_id;
        formArticulo.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="eliminar_articulo"]');
        if (modal?.showModal) modal.showModal();
    } else if (modo === 'recuperar') {
        document.getElementById('confirmar_codigo_articulo').textContent = data.articulo_codigo || '';
        document.getElementById('confirmar_nombre_articulo').textContent = data.articulo_nombre || '';
        document.getElementById('confirmar_categoria_articulo').textContent = data.categoria_nombre || '';
        document.getElementById('confirmar_clasificacion_articulo').textContent = data.clasificacion_nombre || '';
        document.getElementById('confirmar_imagen_articulo').src = (data.articulo_imagen?.trim() !== '') 
            ? data.articulo_imagen + '?t=' + Date.now() 
            : 'img/icons/articulo.png';

        const formArticulo = document.getElementById('form_confirmar_articulo');
        formArticulo.dataset.articuloId = data.articulo_id;
        formArticulo.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="confirmar_articulo"]');
        if (modal?.showModal) modal.showModal();
    }
}

// Eliminar
const formDelete = document.getElementById('form_delete_articulo');
if (formDelete) {
    formDelete.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = this.dataset.articuloId;
        if (!id) return;

        fetch('php/articulo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'deshabilitar_articulo', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                const modal = document.querySelector('dialog[data-modal="eliminar_articulo"]');
                if (modal?.open) modal.close();

                mostrarModalExito(data.mensaje || 'Artículo deshabilitado');
                if ($('#articuloTabla').length) {
                    $('#articuloTabla').DataTable().ajax.reload(null, false);
                }
            }
        })
        .catch(() => console.error('Error de conexión con el servidor'));
    });
}

// Recuperar
const formConfirmar = document.getElementById('form_confirmar_articulo');
if (formConfirmar) {
    formConfirmar.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = this.dataset.articuloId;
        if (!id) return;

        fetch('php/articulo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'recuperar_articulo', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                const modal = document.querySelector('dialog[data-modal="confirmar_articulo"]');
                if (modal?.open) modal.close();

                mostrarModalExito(data.mensaje || 'Artículo recuperado');
                if (typeof estadoActual !== 'undefined') {
                    estadoActual = 1;
                }
                if ($('#articuloTabla').length) {
                    $('#articuloTabla').DataTable().ajax.reload(null, false);
                }
            }
        })
        .catch(() => console.error('Error de conexión con el servidor'));
    });
}

// Actualizar tabla al cambiar filtros
document.addEventListener('change', function (e) {
    if ((e.target.matches('#categoria_filtro') || e.target.matches('#clasificacion_filtro'))
        && $('#articuloTabla').length) {
        $('#articuloTabla').DataTable().ajax.reload(null, false);
    }
});



/*SUBMODULO: CARGOS*/

// Función: abrir formulario de edición de cargo
function abrirFormularioEdicionCargo(id) {
    const form = document.getElementById('form_nuevo_cargo');
    if (!form) return;

    fetch('php/cargo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_cargo', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.cargo) return;

        const c = data.cargo;

        // Asignar valores a los inputs del formulario
        form.querySelector('#cargo_id').value = c.cargo_id;
        form.querySelector('[name="cargo_codigo"]').value = c.cargo_codigo;
        form.querySelector('[name="cargo_nombre"]').value = c.cargo_nombre;
        form.querySelector('[name="cargo_descripcion"]').value = c.cargo_descripcion;

        const modal = document.querySelector('[data-modal="new_cargo"]');
        if (modal?.showModal) modal.showModal();
    })
    .catch(err => {
        console.error('Error al obtener cargo:', err);
    });
}

// Crear o actualizar cargo
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form_nuevo_cargo');
    const errorContainer = document.getElementById('error-container-cargo');
    const btnNuevo = document.querySelector('[data-modal-target="new_cargo"]');
    const modalFormulario = document.querySelector('dialog[data-modal="new_cargo"]');

    // Abrir modal y limpiar formulario
    if (btnNuevo && modalFormulario && form) {
        btnNuevo.addEventListener('click', () => {
            limpiarFormulario(form);
            if (modalFormulario?.showModal) modalFormulario.showModal();
        });
    }

    // Envío del formulario
    if (form) {
        form.addEventListener('submit', function (e) {
            console.log("Interceptado submit de cargo");
            e.preventDefault();

            const cargoId = form.querySelector('#cargo_id')?.value || '';
            const formData = new FormData(form);
            const accion = cargoId ? 'actualizar' : 'crear';

            formData.append('accion', accion);
            if (cargoId) formData.append('cargo_id', cargoId);

            fetch('php/cargo_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!errorContainer) return;
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
                    const esActualizacion = !!cargoId;
                    const mensaje = esActualizacion
                        ? "Cargo actualizado con éxito"
                        : "Cargo registrado con éxito";

                    // Cerrar modal si fue actualización
                    if (esActualizacion && modalFormulario?.open) {
                        modalFormulario.close();
                    }

                    mostrarModalExito(mensaje);
                    limpiarFormulario(form);
                    if ($('#cargoTabla').length) {
                        $('#cargoTabla').DataTable().ajax.reload(null, false);
                    }
                }
            })
            .catch(() => {
                if (!errorContainer) return;
                errorContainer.innerHTML = 'Hubo un error con el servidor';
                errorContainer.style.display = 'block';
            });
        });
    }
});


// Mostrar información de cargo
function mostrarInfoCargo(data) {
    document.getElementById('info_codigo').textContent = data.cargo_codigo || '';
    document.getElementById('info_nombre').textContent = data.cargo_nombre || '';
    document.getElementById('info_descripcion').textContent = data.cargo_descripcion || '';

    const modal = document.querySelector('dialog[data-modal="info_cargo"]');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
}

// Mostrar datos en confirmación de cargo
function mostrarConfirmacionCargo(data, modo = 'eliminar') {
    if (modo === 'eliminar') {
        document.getElementById('delete_codigo').textContent = data.cargo_codigo || '';
        document.getElementById('delete_nombre').textContent = data.cargo_nombre || '';

        const form = document.getElementById('form_delete_cargo');
        form.dataset.cargoId = data.cargo_id;
        form.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="eliminar_cargo"]');
        if (modal?.showModal) modal.showModal();
    } else if (modo === 'recuperar') {
        document.getElementById('confirmar_codigo').textContent = data.cargo_codigo || '';
        document.getElementById('confirmar_nombre').textContent = data.cargo_nombre || '';

        const form = document.getElementById('form_confirmar_cargo');
        form.dataset.cargoId = data.cargo_id;
        form.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="confirmar_cargo"]');
        if (modal?.showModal) modal.showModal();
    }
}

// Eliminar cargo
document.getElementById('form_delete_cargo')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.cargoId;
    if (!id) return;

    fetch('php/cargo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'deshabilitar_cargo', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="eliminar_cargo"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Cargo deshabilitado');
            $('#cargoTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});

// Recuperar cargo
document.getElementById('form_confirmar_cargo')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.cargoId;
    if (!id) return;

    fetch('php/cargo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'recuperar_cargo', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="confirmar_cargo"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Cargo recuperado');
            estadoActual = 1;
            $('#cargoTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});

/*SUBMODULO: ÁREAS*/

// Función: abrir formulario de edición de área
function abrirFormularioEdicionArea(id) {
    const form = document.getElementById('form_nueva_area');
    if (!form) return;

    fetch('php/area_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_area', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.area) return;

        const a = data.area;

        // Asignar valores a los inputs del formulario
        form.querySelector('#area_id').value = a.area_id;
        form.querySelector('[name="area_codigo"]').value = a.area_codigo;
        form.querySelector('[name="area_nombre"]').value = a.area_nombre;
        form.querySelector('[name="area_descripcion"]').value = a.area_descripcion;

        const modal = document.querySelector('[data-modal="new_area"]');
        if (modal?.showModal) modal.showModal();
    })
    .catch(err => {
        console.error('Error al obtener área:', err);
    });
}

// Crear o actualizar área
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form_nueva_area');
    const errorContainer = document.getElementById('error-container-area');
    const btnNuevo = document.querySelector('[data-modal-target="new_area"]');
    const modalFormulario = document.querySelector('dialog[data-modal="new_area"]');

    // Abrir modal y limpiar formulario
    if (btnNuevo && modalFormulario) {
        btnNuevo.addEventListener('click', () => {
            if (modalFormulario.showModal) modalFormulario.showModal();
            limpiarFormulario(form);
        });
    }

    // Envío del formulario
    if (form) {
        form.addEventListener('submit', function (e) {
            console.log("Interceptado submit de área");
            e.preventDefault();
            const areaId = form.querySelector('#area_id').value;
            const formData = new FormData(form);
            const accion = areaId ? 'actualizar' : 'crear';

            formData.append('accion', accion);
            if (areaId) formData.append('area_id', areaId);

            fetch('php/area_ajax.php', {
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
                    const esActualizacion = !!areaId;
                    const mensaje = esActualizacion
                        ? "Área actualizada con éxito"
                        : "Área registrada con éxito";

                    // Cerrar modal si fue actualización
                    if (esActualizacion && modalFormulario && modalFormulario.open) {
                        modalFormulario.close();
                    }

                    mostrarModalExito(mensaje);
                    limpiarFormulario(form);
                    $('#areaTabla').DataTable().ajax.reload(null, false);
                }
            })
            .catch(() => {
                errorContainer.innerHTML = 'Hubo un error con el servidor';
                errorContainer.style.display = 'block';
            });
        });
    }
});

// Mostrar información de área
function mostrarInfoArea(data) {
    document.getElementById('info_codigo_area').textContent = data.area_codigo || '';
    document.getElementById('info_nombre_area').textContent = data.area_nombre || '';
    document.getElementById('info_descripcion_area').textContent = data.area_descripcion || '';

    const modal = document.querySelector('dialog[data-modal="info_area"]');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
}

// Mostrar datos en confirmación de área
function mostrarConfirmacionArea(data, modo = 'eliminar') {
    if (modo === 'eliminar') {
        document.getElementById('delete_codigo_area').textContent = data.area_codigo || '';
        document.getElementById('delete_nombre_area').textContent = data.area_nombre || '';
        document.getElementById('delete_descripcion_area').textContent = data.area_descripcion || '';

        const form = document.getElementById('form_delete_area');
        form.dataset.areaId = data.area_id;
        form.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="eliminar_area"]');
        if (modal?.showModal) modal.showModal();
    } else if (modo === 'recuperar') {
        document.getElementById('confirmar_codigo_area').textContent = data.area_codigo || '';
        document.getElementById('confirmar_nombre_area').textContent = data.area_nombre || '';
        document.getElementById('confirmar_descripcion_area').textContent = data.area_descripcion || '';

        const form = document.getElementById('form_confirmar_area');
        form.dataset.areaId = data.area_id;
        form.dataset.modo = modo;

        const modal = document.querySelector('dialog[data-modal="confirmar_area"]');
        if (modal?.showModal) modal.showModal();
    }
}

// Eliminar área
document.getElementById('form_delete_area')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.areaId;
    if (!id) return;

    fetch('php/area_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'deshabilitar_area', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="eliminar_area"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Área deshabilitada');
            $('#areaTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});

// Recuperar área
document.getElementById('form_confirmar_area')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.dataset.areaId;
    if (!id) return;

    fetch('php/area_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'recuperar_area', id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            const modal = document.querySelector('dialog[data-modal="confirmar_area"]');
            if (modal?.open) modal.close();

            mostrarModalExito(data.mensaje || 'Área recuperada');
            estadoActual = 1; // si usas esta variable para refrescar estado
            $('#areaTabla').DataTable().ajax.reload(null, false);
        }
    })
    .catch(() => {
        console.error('Error de conexión con el servidor');
    });
});

/*SUBMODULO: PERSONAL*/

// Función: formulario de actualizar personal
function abrirFormularioEdicionPersona(id) {
    const form = document.getElementById('form_nuevo_personal');
    if (!form) return;

    fetch('php/personal_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_persona', persona_id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.persona) return;

        const p = data.persona;

        // Limpiar antes de repoblar
        limpiarFormulario(form);

        // Poblar cargos y seleccionar el actual
        cargarCargo({
            selected: p.cargo_id,
            scope: 'form',
            onComplete: () => {
                form.persona_id.value = p.persona_id;
                form.persona_nombre.value = p.persona_nombre;
                form.persona_apellido.value = p.persona_apellido;
                form.persona_correo.value = p.persona_correo;
                form.persona_telefono.value = p.persona_telefono;
                form.persona_direccion.value = p.persona_direccion;
                form.persona_nac.value = p.persona_nac;
                form.persona_sexo.value = p.persona_sexo;
                form.persona_tipo_cedula.value = p.persona_cedula.split('-')[0];
                document.getElementById('persona_numero_cedula').value = p.persona_cedula.split('-')[1];

                const previewFoto = document.getElementById('preview_persona_foto');
                const icono = document.querySelector('.foto_perfil_icon');
                previewFoto.src = p.persona_foto || 'img/icons/personal.png';
                previewFoto.style.display = 'block';
                icono.style.opacity = '0';

                const modal = document.querySelector('[data-modal="new_personal"]');
                if (modal?.showModal) modal.showModal();
            }
        });
    });
}

// Crear o actualizar personal
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form_nuevo_personal');
    const errorContainer = document.getElementById('error-container-persona');
    const inputFoto = document.getElementById('persona_foto');
    const previewFoto = document.getElementById('preview_persona_foto');
    const icono = document.querySelector('.foto_perfil_icon');
    const btnNuevo = document.querySelector('[data-modal-target="new_personal"]');
    const modalFormulario = document.querySelector('dialog[data-modal="new_personal"]');

    // Abrir modal y cargar cargos
    if (btnNuevo && modalFormulario) {
        btnNuevo.addEventListener('click', () => {
            if (modalFormulario.showModal) modalFormulario.showModal();
            cargarCargo({ scope: 'form' }); // poblar el select de cargos
            limpiarFormulario(form);
        });
    }

    // Previsualizar imagen
    if (inputFoto && previewFoto && icono) {
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
    }

    // Envío del formulario
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const tipo = document.getElementById('persona_tipo_cedula').value;
            const numeroInput = document.getElementById('persona_numero_cedula');
            const numero = numeroInput.value.trim();
            const personaId = document.getElementById('persona_id').value;

            const cedulaCompleta = tipo + '-' + numero;
            const formData = new FormData(form);
            formData.set('persona_cedula', cedulaCompleta);

            // Acción condicional
            const accion = personaId ? 'actualizar' : 'crear';
            formData.append('accion', accion);
            if (personaId) formData.append('persona_id', personaId);

            fetch('php/personal_ajax.php', {
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
                            if (campo === 'persona_cedula') input = document.getElementById('persona_numero_cedula');
                            if (campo === 'persona_foto') input = inputFoto;
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
                    const esActualizacion = !!personaId;
                    const mensaje = esActualizacion
                        ? "Personal actualizado con éxito"
                        : "Personal registrado con éxito";

                    // Solo cerrar el modal si fue una actualización
                    if (esActualizacion && modalFormulario && modalFormulario.open) {
                        modalFormulario.close();
                    }

                    mostrarModalExito(mensaje);
                    limpiarFormulario(form);
                    $('#personaTabla').DataTable().ajax.reload(null, false);
                }
            })
            .catch(() => {
                errorContainer.innerHTML = 'Hubo un error con el servidor';
                errorContainer.style.display = 'block';
            });
        });
    }
});


// Función INFO PERSONAL
function mostrarInfoPersona(data) {
    // Foto de perfil
    const foto = data.persona_foto && data.persona_foto.trim() !== ''
        ? data.persona_foto
        : 'img/icons/personal.png';
    document.getElementById('foto_persona_info').src = foto;

    // Formatear fecha de nacimiento
    let fechaFormateada = '';
    if (data.persona_nac) {
        const fecha = new Date(data.persona_nac);
        const dia = String(fecha.getDate()).padStart(2, '0');
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const año = fecha.getFullYear();
        fechaFormateada = `${dia}-${mes}-${año}`;
    }

    // Traducir sexo binario
    const sexo = Number(data.persona_sexo);
    const sexoTraducido = sexo === 0 ? 'M' : sexo === 1 ? 'F' : '';

    // Datos personales
    document.getElementById('info_persona_nombre').textContent = data.persona_nombre || '';
    document.getElementById('info_persona_apellido').textContent = data.persona_apellido || '';
    document.getElementById('info_persona_correo').textContent = data.persona_correo || '';
    document.getElementById('info_persona_telefono').textContent = data.persona_telefono || '';
    document.getElementById('info_persona_cedula').textContent = data.persona_cedula || '';
    document.getElementById('info_persona_nac').textContent = fechaFormateada;
    document.getElementById('info_persona_direccion').textContent = data.persona_direccion || '';
    document.getElementById('info_persona_sexo').textContent = sexoTraducido;
    document.getElementById('info_persona_cargo').textContent = data.cargo_nombre || '';

    // Mostrar el modal
    const modal = document.querySelector('dialog[data-modal="info_persona"]');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
}

// 👉 Función para ingresar seriales con encabezado
function ingresarSerialArticulo(dataBackend, articuloBuffer) {
    // Imagen
    const img = document.getElementById('recepcion_imagen_articulo');
    img.src = (dataBackend.articulo_imagen?.trim() !== '') 
        ? dataBackend.articulo_imagen + '?t=' + Date.now() 
        : 'img/icons/articulo.png';

    // Código y nombre (acepta varias claves)
    const codigo = dataBackend.articulo_codigo || dataBackend.codigo || articuloBuffer.codigo || '';
    const nombre = dataBackend.articulo_nombre || dataBackend.nombre || articuloBuffer.nombre || '';

    document.getElementById('recepcion_codigo_articulo').textContent = codigo;
    document.getElementById('recepcion_nombre_articulo').textContent = nombre;

    // Ocultar/limpiar contenedor de error
    const errorContainer = document.getElementById('error-container-recepcion-serial');
    if (errorContainer) {
        errorContainer.innerHTML = '';
        errorContainer.style.display = 'none';
    }

    // Tabla de seriales
    const tablaSeriales = $('#recepcionSerialIdTabla').DataTable();
    tablaSeriales.clear();

    const cantidad = parseInt(articuloBuffer.cantidad, 10) || 0;
    if (cantidad <= 0) {
        tablaSeriales.rows.add([{ numero: '', serial: 'Intente ingresar cantidad' }]).draw();
    } else {
        const filas = [];
        for (let i = 0; i < cantidad; i++) {
            filas.push({
                numero: i + 1,
                serial: `<input type="text" 
                            class="input_text input_serial" 
                            data-articulo="${articuloBuffer.articulo_id}" 
                            data-index="${i}" 
                            value="${articuloBuffer.seriales[i] || ''}">`
            });
        }
        tablaSeriales.rows.add(filas).draw();
    }

    // Abrir modal
    document.querySelector('dialog[data-modal="seriales_articulo"]')?.showModal();
}


// Eliminar personal
document.getElementById('form_delete_persona')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.dataset.personaId;
    if (!id) return;

    fetch('php/personal_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'deshabilitar_persona', persona_id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            document.querySelector('dialog[data-modal="eliminar_persona"]')?.close();
            mostrarModalExito(data.mensaje || 'Personal deshabilitado');
            $('#personaTabla').DataTable().ajax.reload(null, false);
        }
    });
});

// Recuperar personal
document.getElementById('form_confirmar_persona')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.dataset.personaId;
    if (!id) return;

    fetch('php/personal_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'recuperar_persona', persona_id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            document.querySelector('dialog[data-modal="confirmar_persona"]')?.close();
            mostrarModalExito(data.mensaje || 'Personal recuperado');
            estadoActual = 1;
            $('#personaTabla').DataTable().ajax.reload(null, false);
        }
    });
});

//Filtros de cargos
document.addEventListener('change', function (e) {
    if (e.target.matches('#cargo_filtro')) {
        $('#personaTabla').DataTable().ajax.reload(null, false);
    }
});


/*MODULO: RECEPCION*/

