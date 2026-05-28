<?php
require_once '../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getPortada($ids) {
    global $conn;

    if (!$ids || count($ids) === 0) {
        return [];
    }

    try {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
    SELECT t.id , t.nombre, t.slug, m.menu_slug as tipo, t.imagen_portada
    FROM tours t
    LEFT JOIN idioma_menu im ON t.idTipo = im.id 
    LEFT JOIN menu m ON im.menu_id = m.id
    WHERE t.id IN ($placeholders)
";

        $stmt = $conn->prepare($sql);
        $types = str_repeat('i', count($ids));
        $stmt->bind_param($types, ...$ids);

        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    } catch (Exception $e) {
        error_log("Error al obtener las portadas: " . $e->getMessage());
        return [];
    }
}

if (isset($_GET['ids'])) {
    // Convertir string "1,2,3,4" en array [1,2,3,4]
    $ids = array_map('intval', explode(',', $_GET['ids']));

    $data = getPortada($ids);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "Faltan parámetros"]);
}