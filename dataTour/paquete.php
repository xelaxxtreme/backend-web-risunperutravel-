<?php
require_once '../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getPaquete($id) {
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();
        if (!$row) {
            return null;
        }

        return $row;
    } catch (Exception $e) {
        error_log("Error al obtener el paquete: " . $e->getMessage());
        return null;
    }
}

if (isset($_GET['id'])) {
    $id  = $_GET['id'];

    $data = getPaquete($id);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "Faltan parámetros"]);
}
?>