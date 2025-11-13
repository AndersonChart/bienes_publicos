<?php
require_once 'rol.php';
$rol = new rol();

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'leer_todos':
    try {
        $registros = $rol->leer_todas();
        header('Content-Type: application/json');
        echo json_encode($registros);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'mensaje' => 'Error al leer roles',
            'detalle' => $e->getMessage()
        ]);
    }
break;


    default:
        echo json_encode([
            'error' => true,
            'mensaje' => 'Acción no válida'
        ]);
    break;
}
?>
