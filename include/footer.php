<?php
if ($vista !== 'login') {
  echo '</div>'; // cierre de .content
  echo '</div>'; // cierre de .inicio-background
}


if ($vista == 'login') {
    echo '<script src="js/validacion_usuario.js"></script>';
}

if ($vista == 'form_registrar_bien' || $vista == 'form_actualizar_bien') {
    echo '<script src="js/validacion_bien.js"></script>';
}

if ($vista == 'form_registrar_marca' || $vista == 'form_actualizar_marca') {
    echo '<script src="js/validacion_marca.js"></script>';
}

if ($vista == 'form_registrar_categoria' || $vista == 'form_actualizar_categoria') {
    echo '<script src="js/validacion_categoria.js"></script>';
}

if ($vista == 'form_registrar_modelo' || $vista == 'form_actualizar_modelo') {
    echo '<script src="js/validacion_modelo.js"></script>';
}

?>

<script src="js/interfaz.js"></script>
</body>
</html>
