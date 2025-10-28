//Función global: operación exitosa
function mostrarModalExito(mensaje) {
    const successModal = document.querySelector('dialog[data-modal="success"]');
    const successMessage = document.getElementById('success-message');
    const closeSuccess = document.getElementById('close-success');

    if (successModal && typeof successModal.showModal === 'function') {
        successMessage.textContent = mensaje;
        successModal.showModal();
        closeSuccess.onclick = () => successModal.close();
    }
}

//Función global: lista de usuario
function cargarUsuarios() {
    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'leer_todos' })
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);

        const contenedor = document.querySelector('.grid.grid-usuario');
        if (!contenedor) {
            console.error('No se encontró el contenedor .grid.grid-usuario');
            return;
        }

        
        //Obtener el rol de usuario para los permisos
        const usuarioRol = document.getElementById('usuario')?.dataset.id;

        const filasAnteriores = contenedor.querySelectorAll('.row');
        filasAnteriores.forEach(fila => fila.remove());

        if (Array.isArray(data) && data.length > 0) {
    data.forEach(usuario => {
        const telefono = usuario.usuario_telefono || '';
        const foto = usuario.usuario_foto || 'img/icons/perfil.png';

        contenedor.innerHTML += `
            <div class="row">${usuario.usuario_id}</div>
            <div class="row">${usuario.usuario_nombre}</div>
            <div class="row">${usuario.usuario_apellido}</div>
            <div class="row">${usuario.usuario_cedula}</div>
            <div class="row">${usuario.usuario_correo}</div>
            <div class="row">${telefono}</div>
            <div class="row"><img src="${foto}" alt="Foto" width="40"></div>
            <div class="row">
                ${
                    usuarioRol === "2"
                    ? `
                        <div class="icon-action actualizar" data-url="form_actualizar_usuario.php" data-id="${usuario.usuario_id}" data-action="actualizar" title="Actualizar">
                            <img src="img/icons/actualizar.png" alt="Actualizar">
                        </div>
                        <div class="icon-action info" data-url="info_usuario.php" data-id="${usuario.usuario_id}" data-action="info" title="Info">
                            <img src="img/icons/info.png" alt="Info">
                        </div>
                        <div class="icon-action eliminar" data-id="${usuario.usuario_id}" data-action="eliminar" title="Eliminar">
                            <img src="img/icons/eliminar.png" alt="Eliminar">
                        </div>
                    `
                    : `<span class="text-empty">Ninguno</span>`
                }
            </div>
        `;
    });
} else {
            contenedor.innerHTML += `<div class="text-empty">No hay ningún registro</div>`;
        }
    }) 
    .catch(err => {
        console.error('Error AJAX:', err);
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

    //Limpiar formularios al cerrarlos con modal__close
    document.querySelectorAll('.modal__close').forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = btn.closest('dialog');
        if (!modal) return;

        // Buscar el formulario real (con id) dentro del modal
        const form = modal.querySelector('form[id]');
        if (form) {
            form.reset();

            // Limpiar errores visuales
            form.querySelectorAll('.input-error').forEach(el => {
                el.classList.remove('input-error');
            });

            // Limpiar mensaje de error
            const errorContainer = form.querySelector('.error-container');
            if (errorContainer) {
                errorContainer.innerHTML = '';
                errorContainer.style.display = 'none';
            }

            // Limpiar imágenes de previsualización
            form.querySelectorAll('img[id^="preview_"]').forEach(img => {
                img.removeAttribute('src');
                img.style.display = 'none';
            });

            // Restaurar íconos "+" si existen
            form.querySelectorAll('.foto_perfil_icon').forEach(icono => {
                icono.style.opacity = '1';
            });
        }

        // Cerrar el modal (por si no se cierra automáticamente)
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

    const botones = document.querySelectorAll('[data-modal-target]');
    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            const modalID = boton.getAttribute('data-modal-target');
            const modal = document.querySelector(`[data-modal="${modalID}"]`);
            if (modal) modal.showModal();
        });
    });

    const btncrear = document.getElementById('btn_crear');
    if (btncrear) btncrear.disabled = false;

    //Colocar la vista correspondiente
    const vistaActual = new URL(window.location.href).searchParams.get('vista');

    if (vistaActual === 'listar_usuario') {
        cargarUsuarios();
    } else if (vistaActual === 'listar_bien') {
        cargarBienes();
    } else if (vistaActual === 'listar_marca') {
        cargar();
    }
    // Puede seguirse agregando más vistas aquí

});

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

    // Limpiar errores visuales previos
    form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
    errorContainer.innerHTML = '';
    errorContainer.style.display = 'none';

    // Enviar datos al backend
    const cedulaCompleta = tipo + '-' + numero;
    const formData = new FormData(form);
    formData.set('usuario_cedula', cedulaCompleta); // solo en el envío
    formData.append('rol_id', '1');
    formData.append('accion', 'crear');

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
            mostrarModalExito("Usuario registrado con éxito");
            form.reset();
            previewFoto.removeAttribute('src');
            previewFoto.style.display = 'none';
            icono.style.opacity = '1';
            errorContainer.innerHTML = '';
            errorContainer.style.display = 'none';
            cargarUsuarios();
        }
    })
    .catch(() => {
        errorContainer.innerHTML = 'Hubo un error con el servidor';
        errorContainer.style.display = 'block';
    });
});
});
