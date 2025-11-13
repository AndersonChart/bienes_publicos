<?php
require_once '../bd/conexion.php';
session_start();

class usuario {

    //Estas dos primeras sentencias son obligatorias para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    public function existeCorreo($correo, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM usuario WHERE usuario_correo = ?";
        $params = [$correo];

        if ($excluirId !== null) {
            $sql .= " AND usuario_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function existeCedula($cedula, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM usuario WHERE usuario_cedula = ?";
        $params = [$cedula];

        if ($excluirId !== null) {
            $sql .= " AND usuario_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function existeUsuario($usuario, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM usuario WHERE usuario_usuario = ?";
        $params = [$usuario];

        if ($excluirId !== null) {
            $sql .= " AND usuario_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Para crear nuevo registro
    public function crear($nombre,$apellido,$correo,$telefono,$cedula,$nac,$direccion,$sexo,$clave,$usuario,$rol,$foto,$estado) {
        $stmt = $this->pdo->prepare("INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_correo, usuario_telefono, usuario_cedula, usuario_sexo, usuario_nac, usuario_direccion, usuario_clave, usuario_usuario, rol_id, usuario_foto, usuario_estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nombre,$apellido,$correo,$telefono,$cedula,$nac,$direccion,$sexo,$clave,$usuario,$rol,$foto,$estado]);
    }

    // Para generar las listas
    public function leer_por_estado($estado = 1) {
        $stmt = $this->pdo->prepare("SELECT u.*, r.rol_nombre FROM usuario u JOIN rol r ON u.rol_id = r.rol_id WHERE u.usuario_id != ? AND u.usuario_estado = ? AND (u.rol_id = 1 OR u.rol_id = 2)");
        $stmt->execute([$_SESSION["id"], $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.rol_nombre
            FROM usuario u
            LEFT JOIN rol r ON u.rol_id = r.rol_id
            WHERE u.usuario_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Actualizar registro
    public function actualizar($nombre,$apellido,$correo,$telefono,$cedula,$nac,$direccion,$sexo,$clave,$usuario,$rol,$foto,$estado,$id) {
        error_log("Actualizando usuario ID $id con datos: " . json_encode(func_get_args()));
        $stmt = $this->pdo->prepare("UPDATE usuario SET usuario_nombre = ?, usuario_apellido = ?, usuario_correo = ?, usuario_telefono = ?, usuario_cedula = ?, usuario_nac = ?, usuario_direccion = ?, usuario_sexo = ?, usuario_clave = ?, usuario_usuario = ?, rol_id = ?, usuario_foto = ?, usuario_estado = ? WHERE usuario_id = ?");
        return $stmt->execute([$nombre,$apellido,$correo,$telefono,$cedula,$nac,$direccion,$sexo,$clave,$usuario,$rol,$foto,$estado,$id]);
    }

    // Desincorporar registro
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE usuario SET usuario_estado = 0 WHERE usuario_id = ?");
        return $stmt->execute([$id]);
    }

    // Recuperar registro
    public function recuperar($id) {
        $stmt = $this->pdo->prepare("UPDATE usuario SET usuario_estado = 1 WHERE usuario_id = ?");
        return $stmt->execute([$id]);
    }
}
?>