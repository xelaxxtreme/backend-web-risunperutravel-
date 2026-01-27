<?php
require_once '../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getCards($lang) {
    global $conn; // asumimos que $conn viene de bd.php

    try {
        // Preparamos la consulta
        $stmt = $conn->prepare("
            SELECT id, nombre, slug, idioma, tipo, precio_enganche, imagen_portada, picos, dificultad, duracion, departamento
            FROM tours
            WHERE idioma = ?
            ORDER BY RAND()
            LIMIT 12
        ");
        $stmt->bind_param("s", $lang);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();

        return $rows;
    } catch (Exception $e) {
        error_log("Error al obtener los tours: " . $e->getMessage());
        return null;
    }
}

if (isset($_GET['lang'])) {
    $lang  = $_GET['lang'];

    $data = getCards($lang);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "Faltan parámetros"]);
}


?>