<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class persona {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Verificar duplicados por correo
    public function existeCorreo($correo, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM persona WHERE persona_correo = ?";
        $params = [$correo];

        if ($excluirId !== null) {
            $sql .= " AND persona_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Verificar duplicados por cédula
    public function existeCedula($cedula, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM persona WHERE persona_cedula = ?";
        $params = [$cedula];

        if ($excluirId !== null) {
            $sql .= " AND persona_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Crear nuevo registro
    public function crear($nombre, $apellido, $cargoId, $correo, $telefono, $cedula, $sexo, $nac, $direccion, $foto, $estado) {
        $stmt = $this->pdo->prepare("
            INSERT INTO persona (persona_nombre, persona_apellido, cargo_id, persona_correo, persona_telefono, persona_cedula, persona_sexo, persona_nac, persona_direccion, persona_foto, persona_estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$nombre, $apellido, $cargoId, $correo, $telefono, $cedula, $sexo, $nac, $direccion, $foto, $estado]);
    }

    // Listar por estado
    public function leer_por_estado($estado = 1) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.cargo_nombre 
            FROM persona p
            JOIN cargo c ON p.cargo_id = c.cargo_id
            WHERE p.persona_estado = ?
            ORDER BY p.persona_apellido ASC
        ");
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.cargo_nombre
            FROM persona p
            LEFT JOIN cargo c ON p.cargo_id = c.cargo_id
            WHERE p.persona_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($nombre, $apellido, $cargoId, $correo, $telefono, $cedula, $sexo, $nac, $direccion, $foto, $estado, $id) {
        error_log("Actualizando persona ID $id con datos: " . json_encode(func_get_args()));
        $stmt = $this->pdo->prepare("
            UPDATE persona 
            SET persona_nombre = ?, persona_apellido = ?, cargo_id = ?, persona_correo = ?, persona_telefono = ?, persona_cedula = ?, persona_sexo = ?, persona_nac = ?, persona_direccion = ?, persona_foto = ?, persona_estado = ?
            WHERE persona_id = ?
        ");
        return $stmt->execute([$nombre, $apellido, $cargoId, $correo, $telefono, $cedula, $sexo, $nac, $direccion, $foto, $estado, $id]);
    }

    // Desincorporar registro
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE persona SET persona_estado = 0 WHERE persona_id = ?");
        return $stmt->execute([$id]);
    }

    // Recuperar registro
    public function recuperar($id) {
        $stmt = $this->pdo->prepare("UPDATE persona SET persona_estado = 1 WHERE persona_id = ?");
        return $stmt->execute([$id]);
    }
}
?>
