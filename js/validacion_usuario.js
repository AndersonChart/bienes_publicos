document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.querySelector('form[action*="login"]');
    if (!formulario) return;

    formulario.addEventListener('submit', function(e) {
        // Usamos el contenedor de errores compartido con PHP
        let contenedor = document.getElementById('error-container');
        if (!contenedor) {
            contenedor = document.createElement("div");
            contenedor.id = "error-container";
            contenedor.className = "error-container";
            contenedor.style.display = "none"; // Oculto por defecto
            this.insertBefore(contenedor, this.querySelector('.login_submit'));
        }

        contenedor.innerHTML = "";
        contenedor.style.display = "none";

        // 🔍 Captura de campos
        let usuario = this.usuario_usuario.value.trim();
        let clave = this.usuario_clave.value;

        // 🛑 Validación de campos vacíos primero
        if (usuario === "" && clave === "") {
            e.preventDefault();
            contenedor.textContent = "Debe completar todos los campos.";
            contenedor.style.display = "block";
            return;
        }

        if (usuario === "") {
            e.preventDefault();
            contenedor.textContent = "El campo de usuario o correo electrónico es obligatorio.";
            contenedor.style.display = "block";
            return;
        }

        if (clave === "") {
            e.preventDefault();
            contenedor.textContent = "La contraseña es obligatoria.";
            contenedor.style.display = "block";
            return;
        }

        // 🔐 Validación de usuario o correo
        if (/\s/.test(usuario)) {
            e.preventDefault();
            contenedor.textContent = "El usuario o correo no puede contener espacios.";
            contenedor.style.display = "block";
            return;
        }

        if (usuario.includes("@") && usuario.length > 200) {
            e.preventDefault();
            contenedor.textContent = "El correo electrónico no puede tener más de 200 caracteres.";
            contenedor.style.display = "block";
            return;
        }

        if (!usuario.includes("@") && usuario.length < 8) {
            e.preventDefault();
            contenedor.textContent = "El nombre de usuario debe tener al menos 8 caracteres.";
            contenedor.style.display = "block";
            return;
        }

        // 🔐 Validación de contraseña
        if (clave.length < 8) {
            e.preventDefault();
            contenedor.textContent = "La contraseña debe tener al menos 8 caracteres.";
            contenedor.style.display = "block";
            return;
        }

        if (/^\s+$/.test(clave)) {
            e.preventDefault();
            contenedor.textContent = "La contraseña no puede estar compuesta solo por espacios.";
            contenedor.style.display = "block";
            return;
        }

        // ✅ Si todo está bien, el formulario se envía y el backend valida usuario y clave
    });
});



