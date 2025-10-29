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


//Función: formulario de actualizar usuario
function abrirFormularioEdicion(id) {
    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({
        accion: 'obtener_usuario',
        id: id
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito || !data.usuario) return;

        const u = data.usuario;
        const form = document.getElementById('form_nuevo_usuario');
        if (!form) return;

        form.usuario_id.value = u.usuario_id; // ✅ aquí se asigna el ID oculto

        // Rellenar otros campos...
        form.nombre.value = u.usuario_nombre;
        form.apellido.value = u.usuario_apellido;
        // ...

        // Mostrar el modal
        const modal = document.querySelector('[data-modal="new_user"]');
        if (modal) modal.showModal();
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

    //Para abrir ventanas modales que tengan el mismo data-modal-target
    document.addEventListener('click', function (e) {
    const boton = e.target.closest('[data-modal-target]');
    if (boton) {
        const modalID = boton.getAttribute('data-modal-target');
        const modal = document.querySelector(`[data-modal="${modalID}"]`);
        if (modal) modal.showModal();
    }
    });


    const btncrear = document.getElementById('btn_crear');
    if (btncrear) btncrear.disabled = false;

    //Colocar la vista correspondiente
    
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
