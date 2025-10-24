<?php
header('Content-Type: application/json');

require_once 'usuario.php';
$usuario = new usuario();

// Verifica que se haya enviado una acción
$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'leer_todos':
        try {
            $registros = $usuario->leer_todos();
            echo json_encode($registros);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al leer usuarios',
                'detalle' => $e->getMessage()
            ]);
        }
        break;

    // Puedes agregar más casos aquí para otras acciones AJAX
    // case 'crear':
    // case 'actualizar':
    // case 'eliminar':
    // etc.

    default:
        echo json_encode([
            'error' => true,
            'mensaje' => 'Acción no válida'
        ]);
        break;
}
