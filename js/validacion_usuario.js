document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.querySelector('form[action*="login"]');
    if (!formulario) return;

    formulario.addEventListener('submit', function(e) {
        let errores = [];
        let contenedor = this.querySelector('.form-resultado');
        if (!contenedor) {
            contenedor = document.createElement("div");
            contenedor.className = "form-resultado";
            this.appendChild(contenedor);
        }
        contenedor.innerHTML = "";

        // 🔐 Validación de usuario o correo
        let usuario = this.usuario.value.trim();
        if (usuario === "") {
            errores.push("El campo de usuario o correo electrónico es obligatorio.");
        } else {
            if (/\s/.test(usuario)) {
                errores.push("El usuario o correo no puede contener espacios.");
            } else if (usuario.includes("@")) {
                if (usuario.length > 200) {
                    errores.push("El correo electrónico no puede tener más de 200 caracteres.");
                }
            } else {
                if (usuario.length < 2) {
                    errores.push("El nombre de usuario debe tener al menos 2 caracteres.");
                }
            }
        }

        // 🔐 Validación de contraseña
        let clave = this.clave.value;
        if (clave === "") {
            errores.push("La contraseña es obligatoria.");
        } else {
            if (clave.length < 5) {
                errores.push("La contraseña debe tener al menos 5 caracteres.");
            }
            if (/^\s+$/.test(clave)) {
                errores.push("La contraseña no puede estar compuesta solo por espacios.");
            }
        }

        // 🚫 Mostrar errores si existen
        if (errores.length > 0) {
            e.preventDefault();
            contenedor.innerHTML = `<div class="alert alert-danger">${errores.join('<br>')}</div>`;
        }
    });
});
