<?php
// ---------------------------------------------
// visor_pdf.php  –  Visor universal de reportes UNES
// ---------------------------------------------

// Validar parámetro obligatorio "file"
if (!isset($_GET['file'])) {
    die("Falta el parámetro 'file' en la URL.");
}

// Sanitizar (evita rutas externas, ../, etc.)
$file = basename($_GET['file']);

// Validar que exista en la carpeta de reportes
$ruta = "reportes/reporte_asignacion.php" . $file;

if (!file_exists($ruta)) {
    die("El reporte solicitado no existe: " . htmlspecialchars($ruta));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UNES - Reporte</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo.png">

    <style>
        body {
            margin: 0;
            background: #e9ecef;
            font-family: Arial, sans-serif;
        }

        .contenedor {
            width: 100vw;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #e9ecef;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .loading {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 8px 15px;
            background: #003366;
            color: #fff;
            border-radius: 4px;
            font-size: 14px;
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

    <div class="loading" id="loading">Cargando reporte…</div>

    <div class="contenedor">
        <iframe src="<?= $ruta ?>" onload="document.getElementById('loading').style.display='none'"></iframe>
    </div>

</body>
</html>
