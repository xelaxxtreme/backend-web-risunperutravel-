<?php
require_once '../../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $sql = "SELECT * FROM restaurantes";
        $result = $conn->query($sql);

        $restaurantes = [];

        while ($row = $result->fetch_assoc()) {
            $row['numero'] = json_decode($row['numero'], true);
            $row['detalles'] = json_decode($row['detalles'], true);
            $restaurantes[] = $row;
        }

        echo json_encode($restaurantes);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error al obtener restaurantes"]);
    }
    exit;
}

if ($method === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        $nombre = $data['nombre'] ?? '';
        $numero = json_encode($data['numero'] ?? []);
        $lugar = $data['lugar'] ?? '';
        $detalles = json_encode($data['detalles'] ?? []);
        $creado = $data['creado'] ?? date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO restaurantes (nombre, numero, lugar, detalles, creado) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $numero, $lugar, $detalles, $creado);
        $stmt->execute();

        $insertedId = $stmt->insert_id;

        http_response_code(201);
        echo json_encode([
            "id" => $insertedId,
            "nombre" => $nombre,
            "numero" => json_decode($numero, true),
            "lugar" => $lugar,
            "detalles" => json_decode($detalles, true),
            "creado" => $creado
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error al crear restaurante"]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Método no permitido"]);
?>