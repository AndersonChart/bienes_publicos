<?php
require_once 'estado.php';
$estado = new estado();

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'leer_todos':
        try {
            $registros = $estado->leer_todas();
            header('Content-Type: application/json');
            echo json_encode($registros);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al leer estados',
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
