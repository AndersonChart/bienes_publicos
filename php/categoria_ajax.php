<?php
require_once 'categoria.php';
$categoria = new categoria();

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'leer_todas':
    try {
        $registros = $categoria->leer_todas();
        header('Content-Type: application/json');
        echo json_encode($registros);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'mensaje' => 'Error al leer categorías',
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
