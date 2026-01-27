<?php
require_once '../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getItemsSitemap() {
    global $conn;  

    try {
        $sql = "SELECT t.id, t.idioma, m.menu_slug as tipo, t.slug FROM tours t INNER JOIN idioma_menu im ON im.id = t.idTipo INNER JOIN menu m ON m.id = im.menu_id ORDER BY t.id ASC";
        $result = $conn->query($sql);

        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    } catch (Exception $e) {
        error_log("❌ Error al obtener los items del sitemap: " . $e->getMessage());
        return [];
    }
}

// 🚀 Aquí devolvemos la respuesta en JSON
$data = getItemsSitemap();
echo json_encode($data, JSON_UNESCAPED_UNICODE);