<?php
require_once '../bd/conexion.php';
session_start();

class usuario {

    //Estas dos primeras sentencias son obligatorias para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Para crear nuevo registro
    public function crear($nombre,$apellido,$correo,$telefono,$cedula,$nac,$direccion,$sexo,$clave,$usuario,$rol,$foto,$estado) {
        $stmt = $this->pdo->prepare("INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_correo, usuario_telefono, usuario_cedula, usuario_sexo, usuario_nac, usuario_direccion, usuario_clave, usuario_usuario, rol_id, usuario_foto, usuario_estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nombre,$apellido,$correo,$telefono,$cedula,$nac,$direccion,$sexo,$clave,$usuario,$rol,$foto,$estado]);
    }

public function leer_todos() {
    $stmt = $this->pdo->prepare("SELECT u.*, r.rol_nombre FROM usuario u JOIN rol r ON u.rol_id = r.rol_id WHERE u.usuario_id != ? AND u.usuario_estado = 1 AND u.rol_id = 1");
    $stmt->execute([$_SESSION["id"]]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE usuario_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($nombre,$apellido,$correo,$telefono,$cedula,$sexo,$clave,$usuario,$rol,$foto,$estado,$id) {
        $stmt = $this->pdo->prepare("UPDATE usuario SET usuario_nombre = ?, usuario_apellido = ?, usuario_correo = ?, usuario_telefono = ?, usuario_cedula = ?, usuario_sexo = ?, usuario_clave = ?, usuario_usuario = ?, rol_id = ?, usuario_foto = ?, usuario_estado = ? WHERE usuario_id = ?");
        return $stmt->execute([$nombre,$apellido,$correo,$telefono,$cedula,$sexo,$clave,$usuario,$rol,$foto,$estado,$id]);
    }

    // Desincorporar registro
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE bien SET estado_id = 4 WHERE bien_id = ?");
        return $stmt->execute([$id]);
    }
}
?>