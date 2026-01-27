<?php
require_once '../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getBuscar($texto, $lang) {
    global $conn;

    try {
        $likeTexto = "%" . $texto . "%";

        $sql = "
            SELECT 
                id,
                nombre,
                precio_enganche,
                descripcion,
                imagen_portada,
                duracion,
                departamento,
                slug,
                tipo
            FROM tours
            WHERE idioma = ?
              AND (
                  nombre LIKE ?
                  OR descripcion LIKE ?
                  OR itinerario LIKE ?
              )
            ORDER BY nombre ASC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $lang, $likeTexto, $likeTexto, $likeTexto);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    } catch (Exception $e) {
        error_log("Error al buscar tours: " . $e->getMessage());
        return [];
    }
}

if (isset($_GET['texto']) && isset($_GET['lang'])) {
    $texto = $_GET['texto'];
    $lang  = preg_replace('/[^a-z]/', '', $_GET['lang']); // sanitizar

    $data = getBuscar($texto, $lang);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} else {
    echo json_encode(["error" => "Faltan parámetros"]);
    exit;
}