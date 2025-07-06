// Selecciona todos los formularios con la clase "FormularioAjax" en la página.
// Esto permite aplicar la funcionalidad AJAX solo a los formularios que la requieran.
const formularios_ajax = document.querySelectorAll(".FormularioAjax");

// Función que gestiona el envío del formulario utilizando AJAX.
function enviar_formulario_ajax(e) {
    // Previene el comportamiento por defecto del formulario (recargar la página).
    e.preventDefault();

    // Muestra una ventana de confirmación al usuario para asegurarse que desea enviar el formulario.
    let enviar = confirm("Quieres enviar el formulario");

    // Si el usuario confirma el envío...
    if (enviar == true) {

        // Crea un objeto FormData con los datos del formulario actual.
        // "this" hace referencia al formulario que disparó el evento.
        let data = new FormData(this);

        // Obtiene el método (GET, POST, etc.) especificado en el formulario.
        let method = this.getAttribute("method");
        
        // Obtiene la URL de destino especificada en el formulario.
        let action = this.getAttribute("action");

        // Crea un objeto Headers vacío (puedes agregar cabeceras personalizadas si es necesario).
        let encabezados = new Headers();

        // Configuración para la petición fetch.
        let config = {
            method: method,        // Método HTTP del formulario.
            headers: encabezados,  // Cabeceras HTTP.
            mode: 'cors',          // Permite solicitudes entre distintos orígenes.
            cache: 'no-cache',     // Desactiva caché de la solicitud.
            body: data             // Envía los datos del formulario.
        };

        // Realiza la petición AJAX usando fetch.
        fetch(action, config)
            // Convierte la respuesta a texto.
            .then(respuesta => respuesta.text())
            // Inserta la respuesta del servidor dentro del contenedor con clase "form-rest".
            .then(respuesta => {
                let contenedor = document.querySelector(".form-resultado");
                contenedor.innerHTML = respuesta;
            });
    }
}

// Añade el evento "submit" a cada formulario seleccionado.
// Cada vez que se intente enviar un formulario con la clase "FormularioAjax",
// se ejecutará la función enviar_formulario_ajax.
formularios_ajax.forEach(formularios => {
    formularios.addEventListener("submit", enviar_formulario_ajax);
});