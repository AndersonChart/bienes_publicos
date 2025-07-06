document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.querySelector('form[action*="registrar_marca"]');
    if(!formulario) return;
    formulario.addEventListener('submit', function(e) {
        let errores = [];
        let contenedor = this.querySelector('.form-resultado');
        if(!contenedor){
            contenedor = document.createElement("div");
            contenedor.className = "form-resultado";
            this.appendChild(contenedor);
        }
        contenedor.innerHTML = "";

        // Validar nombre (obligatorio, mínimo 3 caracteres, sin caracteres especiales)
        let nombre = this.nombre.value.trim();
        if (nombre === "") {
            errores.push("El nombre de la marca es obligatorio.");
        }
        if (nombre.length > 0 && nombre.length < 3) {
            errores.push("El nombre debe tener al menos 3 caracteres.");
        }
        if (nombre.length > 0 && !/^[a-zA-Z0-9\s]+$/.test(nombre)) {
            errores.push("El nombre solo puede tener letras, números y espacios.");
        }

        // Validar imagen (opcional pero si hay, debe ser imagen)
        let imagen = this.querySelector('input[type="file"]#img');
        if (imagen && imagen.files.length > 0) {
            let file = imagen.files[0];
            // solo imágenes permitidas
            if (!file.type.match(/^image\//)) {
                errores.push("La imagen debe ser un archivo de imagen válido.");
            }
        }

        if (errores.length > 0) {
            e.preventDefault();
            contenedor.innerHTML = `<div class="alert alert-danger">${errores.join('<br>')}</div>`;
        }
    });
});