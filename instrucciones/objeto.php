<?php
require_once 'db.php';

// Cambia "Objeto" por el nombre real de tu objeto de trabajo (Responsable, Ubicacion, Categoria, etc)
class objeto {

    //Estos dos primeros son obligatorios para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Para crear nuevo registro, coloca cuantos campos requieras, pero la cantidad de ? tiene que ser igual al de los campos
    public function crear($campo1, $campo2) {
        $stmt = $this->pdo->prepare("INSERT INTO objeto (campo1, campo2) VALUES (?, ?)");
        return $stmt->execute([$campo1, $campo2]);
    }

    // Leer todos los registros: para enlistar datos. debes colocar el nombre de la tabla correspondiente
    public function leer_todos() {
        $stmt = $this->pdo->query("SELECT * FROM objeto");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM objeto WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($id, $campo1, $campo2) {
        $stmt = $this->pdo->prepare("UPDATE objeto SET campo1 = ?, campo2 = ? WHERE id = ?");
        return $stmt->execute([$campo1, $campo2, $id]);
    }

    // Eliminar registro
    public function eliminar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM objeto WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Sugerencia: aquí puedes agregar métodos para búsquedas, autenticación, etc.
}
?>