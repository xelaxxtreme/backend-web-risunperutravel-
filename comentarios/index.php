<?php
require_once '../config/db.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getTours($tipo) {
    global $conn; 

    try {
        $stmt = $conn->prepare("
            SELECT *
            FROM comentarios
            WHERE tipo = ? 
            ORDER BY fecha DESC 
            LIMIT 12
        ");
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        $comentarios = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $comentarios;

    } catch (Exception $e) {
        error_log("Error al obtener los comentarios: " . $e->getMessage());
        return null;
    }
}

function getUltimosGlobales() {
    global $conn; 

    try {
        $stmt = $conn->prepare("
            SELECT *
            FROM comentarios
            ORDER BY fecha DESC 
            LIMIT 12
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comentarios = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $comentarios;

    } catch (Exception $e) {
        error_log("Error al obtener comentarios globales: " . $e->getMessage());
        return null;
    }
}

if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
    $data = getTours($_GET['tipo']);
} else {
    $data = getUltimosGlobales();
}
if ($data !== null) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["error" => "No se pudieron obtener los comentarios"]);
}

?>