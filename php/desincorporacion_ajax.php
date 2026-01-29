<?php
require_once 'desincorporacion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$desincorporacion = new desincorporacion();
$accion    = $_POST['accion'] ?? '';

function validarDesincorporacion($datos, $modo = 'crear', $id = null) {
    // 1. Campos obligatorios
    $camposObligatorios = [
        'ajuste_fecha' => 'proceso_desincorporacion_fecha', 
        'ajuste_nombre_original' => 'acta_desincorporacion' 
    ];
    
    $camposFaltantes = [];
    foreach ($camposObligatorios as $claveData => $idHtml) {
        // Validamos usando la CLAVE (lo que viene de la petición)
        if (!isset($datos[$claveData]) || trim($datos[$claveData]) === '') {
            $camposFaltantes[] = $idHtml; // Guardamos el ID HTML para el borde rojo
        }
    }

    if (!empty($camposFaltantes)) {
        return [
            'error'   => true,
            'mensaje' => 'Faltan datos obligatorios',
            'campos'  => $camposFaltantes
        ];
    }

    // 2. VALIDACIÓN DEL ARCHIVO (Mantenemos tu lógica, está muy bien)
    $nombreArchivo = $datos['ajuste_nombre_original'];
    $extensionesPermitidas = ['pdf', 'xls', 'xlsx'];
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

    if (!in_array($extension, $extensionesPermitidas)) {
        return [
            'error'   => true,
            'mensaje' => 'El formato del acta no es válido. Solo se permite PDF o Excel.',
            'campos'  => ['acta_desincorporacion'] 
        ];
    }

    // 3. Validación de Fecha (Sincronizada con el ID del HTML)
    $fecha = trim($datos['ajuste_fecha']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || $fecha > date('Y-m-d')) {
        return [
            'error'   => true,
            'mensaje' => 'Fecha inválida o futura.',
            'campos'  => ['proceso_desincorporacion_fecha'] // ID corregido aquí
        ];
    }

    // 4. Validación de Artículos y Cantidades
    if (!isset($datos['articulos']) || !is_array($datos['articulos']) || empty($datos['articulos'])) {
        return [
            'error'   => true,
            'mensaje' => 'Debe seleccionar al menos un artículo.',
            'campos'  => ['tabla_articulos']
        ];
    }

    foreach ($datos['articulos'] as $index => $art) {
        $cantidad = isset($art['cantidad']) ? (int)$art['cantidad'] : 0;
        if ($cantidad <= 0) {
            return [
                'error'   => true,
                'mensaje' => "Cantidad inválida en la fila " . ($index + 1),
                'campos'  => ["articulos[$index][cantidad]"]
            ];
        }
        
        if (!isset($art['seriales']) || !is_array($art['seriales'])) {
            return [
                'error'   => true,
                'mensaje' => "Faltan datos de seriales en la fila " . ($index + 1),
                'campos'  => ["articulos[$index][seriales]"]
            ];
        }
    }

    return ['valido' => true];
}


// Router de acciones
switch ($accion) {
    //Leer desincorporaciones
    case 'leer_todos':
        try {
            $estado    = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $registros = $desincorporacion->leer_por_estado($estado);

            $data = array_map(function ($row) {
                return [
                    'desincorporacion_id'          => $row['ajuste_id'],
                    'desincorporacion_fecha'       => $row['ajuste_fecha'],
                    'desincorporacion_descripcion' => $row['ajuste_descripcion'],
                    'desincorporacion_estado'      => $row['ajuste_estado']
                ];
            }, $registros);

            echo json_encode(['data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data'    => [],
                'error'   => true,
                'mensaje' => 'Error al leer las desincorporaciones registradas',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'crear':
        try {
            header('Content-Type: application/json');

            // 1. Decodificar artículos
            $articulos = [];
            if (isset($_POST['articulos'])) {
                $decoded = json_decode($_POST['articulos'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $articulos = $decoded;
                }
            }

            // 2. Preparar datos base
            $datos = [
                'ajuste_fecha'           => $_POST['ajuste_fecha'] ?? '',
                'ajuste_descripcion'     => $_POST['ajuste_descripcion'] ?? '',
                'ajuste_tipo'            => 0, // 0 = Desincorporación
                'ajuste_nombre_original' => $_FILES['acta_archivo']['name'] ?? '', // Nombre real del archivo
                'ajuste_nombre_sistema'  => '', // Se llenará si el archivo es válido
                'articulos'              => $articulos
            ];

            // 3. Validación integral (Pasamos $_FILES si es necesario)
            $validacion = validarDesincorporacion($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            // 4. Procesar el Archivo (PDF/Excel)
            if (isset($_FILES['acta_archivo']) && $_FILES['acta_archivo']['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($_FILES['acta_archivo']['name'], PATHINFO_EXTENSION);
                
                // Creamos un nombre único para el sistema (ej: acta_65b2_12345.pdf)
                $nombreSistema = 'acta_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
                $directorio = '../documentos/desincorporaciones/';
                
                if (!is_dir($directorio)) mkdir($directorio, 0755, true);
                
                if (move_uploaded_file($_FILES['acta_archivo']['tmp_name'], $directorio . $nombreSistema)) {
                    $datos['ajuste_nombre_sistema'] = $nombreSistema;
                } else {
                    throw new Exception("No se pudo mover el archivo al servidor.");
                }
            } else {
                // Si el archivo es obligatorio y no llegó
                echo json_encode(['error' => true, 'mensaje' => 'El acta es obligatoria', 'campos' => ['acta_desincorporacion']]);
                exit;
            }

            // 5. Crear Desincorporación en DB
            // Nota: Asegúrate que tu método crear() acepte: ($fecha, $desc, $nombreOrig, $nombreSist, $articulos)
            $recepcionId = $recepcion->crear(
                $datos['ajuste_fecha'],
                $datos['ajuste_descripcion'],
                $datos['ajuste_nombre_original'],
                $datos['ajuste_nombre_sistema'],
                $datos['articulos']
            );

            echo json_encode([
                'exito'   => true,
                'mensaje' => 'La Desincorporación fue registrada correctamente',
                'id'      => $recepcionId
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error'   => true,
                'mensaje' => 'Error al registrar la Desincorporación',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    // Listar artículos disponibles para desincorporación
    case 'listar_articulos_desincorporacion':
        try {
            $categoriaId     = $_POST['categoria_id'] ?? '';
            $clasificacionId = $_POST['clasificacion_id'] ?? '';

            $registros = $desincorporacion->leer_articulos_disponibles(1, $categoriaId, $clasificacionId);

            // Si no hay registros, enviar array vacío pero con estructura DataTables
            if (!$registros) {
                echo json_encode(['data' => []]);
                exit;
            }

            $data = array_map(function ($row) use ($desincorporacion) {
                return [
                    'articulo_id'               => $row['articulo_id'],
                    'articulo_codigo'           => $row['articulo_codigo'],
                    'articulo_nombre'           => $row['articulo_nombre'],
                    'articulo_modelo'           => $row['articulo_modelo'] ?? '',
                    'articulo_imagen'           => $row['articulo_imagen'] ?? '',
                    'clasificacion_nombre'      => $row['clasificacion_nombre'],
                    'categoria_nombre'          => $row['categoria_nombre'],
                    'stock_disponible'          => (int)$desincorporacion->obtener_stock_articulo($row['articulo_id']), 
                    'stock_disponible_seriales' => (int)$desincorporacion->obtener_stock_articulo_seriales($row['articulo_id'])
                ];
            }, $registros);

            echo json_encode(['data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['data' => [], 'error' => true, 'detalle' => $e->getMessage()]);
        }
    break;

    case 'leer_seriales_articulo':
        try {
            $articuloId   = $_POST['id'] ?? '';

            if (!$articuloId) {
                echo json_encode(['data' => [], 'error' => true, 'mensaje' => 'No se proporcionó el identificador del artículo']);
                exit;
            }

            // Si llega asignacion_id, incluir activos (estado 1)
            $registros = $desincorporacion->leer_seriales_articulo($articuloId);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data'    => [],
                'error'   => true,
                'mensaje' => 'Error al listar los seriales del artículo',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    // Obtener detalle de un artículo
    case 'obtener_articulo':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'No se proporcionó el identificador del artículo']);
                exit;
            }
            $articulo = $desincorporacion->leer_articulo_por_id($id);
            echo json_encode($articulo ? ['exito' => true, 'articulo' => $articulo] : ['error' => true, 'mensaje' => 'No se encontró el artículo solicitado']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'mensaje' => 'Error al obtener el artículo', 'detalle' => $e->getMessage()]);
        }
    break;

    default:
        http_response_code(400);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Acción no reconocida en el proceso de desincorporación'
        ]);
    break;
}
