<?php
require_once '../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getVivencial($ids) {
    global $conn; // asumimos que $conn viene de bd.php

    // Si no hay IDs, devolvemos array vacío
    if (!$ids || count($ids) === 0) {
        return [];
    }

    try {
        // Creamos los placeholders dinámicos (?, ?, ?, ...)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
            SELECT id, nombre, slug, tipo
            FROM tours
            WHERE id IN ($placeholders)
        ";

        // Preparamos la consulta
        $stmt = $conn->prepare($sql);

        // Construimos el string de tipos (todos enteros)
        $types = str_repeat('i', count($ids));

        // bind_param necesita referencias
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
        error_log("Error al obtener los tours vivenciales: " . $e->getMessage());
        return [];
    }
}

if (isset($_GET['ids'])) {
    // Convertir string "1,2,3,4" en array [1,2,3,4]
    $ids = array_map('intval', explode(',', $_GET['ids']));

    $data = getVivencial($ids);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "Faltan parámetros"]);
}

?>