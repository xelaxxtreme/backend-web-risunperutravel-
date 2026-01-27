<?php
require_once '../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getMetadata($slug) {
    global $conn; // asumimos que $conn viene de bd.php

    try {
        // Preparamos la consulta
        $stmt = $conn->prepare("
            SELECT id, nombre, slug, idioma, tipo, frase_seo, imagen_portada
            FROM tours
            WHERE slug = ?
        ");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();

        // Obtenemos la primera fila
        $row = $result->fetch_assoc();

        $stmt->close();

        // Si no hay resultados, devolvemos null
        if (!$row) {
            return null;
        }

        return $row;
    } catch (Exception $e) {
        error_log("Error al obtener la metadescripción: " . $e->getMessage());
        return null;
    }
}

if (isset($_GET['slug'])) {
    $slug  = $_GET['slug'];
    $data = getMetadata($slug);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "Faltan parámetros"]);
}

?>