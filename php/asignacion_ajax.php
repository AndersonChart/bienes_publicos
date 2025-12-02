<?php
require_once __DIR__ . '/../bd/conexion.php';
require_once __DIR__ . '/asignacion.php';
header('Content-Type: application/json; charset=utf-8');

$accion = $_POST['accion'] ?? '';
$asig = new asignacion();

try {

    /* =====================================================
       CREAR ASIGNACIÓN
       ===================================================== */
    if ($accion === 'crear') {

        $area = intval($_POST['area_id']);
        $persona = intval($_POST['persona_id']);
        $fecha = $_POST['asignacion_fecha'];
        $fecha_fin = $_POST['asignacion_fecha_fin'] ?: null;

        $seriales = json_decode($_POST['seriales'] ?? '[]', true);

        if (empty($seriales)) {
            echo json_encode(['exito' => false, 'mensaje' => 'Debe agregar seriales']);
            exit;
        }

        $id = $asig->crear($area, $persona, $fecha, $fecha_fin, $seriales);

        echo json_encode([
            'exito' => true,
            'mensaje' => 'Asignación creada correctamente',
            'id' => $id
        ]);
        exit;
    }

    /* =====================================================
       LISTAR ASIGNACIONES PARA DATATABLE
       ===================================================== */
    if ($accion === 'leer_todos') {

        $estado = intval($_POST['estado'] ?? 1);
        $areaId = $_POST['area_id'] ?? '';
        $personaId = $_POST['persona_id'] ?? '';

        $reg = $asig->obtener_todos($estado, $areaId, $personaId);

        echo json_encode(['data' => $reg]);
        exit;
    }

    /* =====================================================
       BUSCAR SERIALES
       ===================================================== */
    if ($accion === 'buscar_seriales') {
        $texto = $_POST['texto'] ?? '';
        $res = $asig->buscar_seriales_disponibles($texto);

        echo json_encode(['data' => $res]);
        exit;
    }

    /* =====================================================
       OBTENER UNA ASIGNACIÓN
       ===================================================== */
    if ($accion === 'obtener_asignacion') {
        $id = intval($_POST['id']);
        $info = $asig->leer_por_id($id);

        echo json_encode(['exito' => true, 'asignacion' => $info]);
        exit;
    }

    /* =====================================================
       FINALIZAR
       ===================================================== */
    if ($accion === 'finalizar') {
        $id = intval($_POST['id']);
        $fecha_fin = $_POST['fecha_fin'];

        $asig->finalizar($id, $fecha_fin, false);

        echo json_encode(['exito' => true]);
        exit;
    }

    /* =====================================================
       RECUPERAR
       ===================================================== */
    if ($accion === 'recuperar') {
        $id = intval($_POST['id']);

        $asig->recuperar($id);

        echo json_encode(['exito' => true]);
        exit;
    }

    echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida']);

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'error' => $e->getMessage()]);
}
