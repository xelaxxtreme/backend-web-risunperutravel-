<?php
require_once '../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getTour($slug) {
    global $conn; 
    
    // 1. Consulta principal con JOINS para traer el slug de la categoría (tipo)
    $stmt = $conn->prepare("
    SELECT 
        t.id, 
        t.nombre, 
        t.slug, 
        t.idioma, 
        t.descripcion, 
        t.precio, 
        t.precio_enganche, 
        t.duracion, 
        t.itinerario, 
        t.departamento, 
        t.incluye, 
        t.no_incluye, 
        t.recomendaciones, 
        t.mapa, 
        t.imagen_portada, 
        t.frase_seo, 
        t.video, 
        t.picos, 
        t.dificultad, 
        t.doc_itinerario,
        m.menu_slug AS tipo, 
        g.id AS galeria_id, 
        SUBSTRING_INDEX(g.nombre, '.', 1) AS imagen_galeria, 
        g.url
    FROM tours t
    LEFT JOIN idioma_menu im ON t.idTipo = im.id
    LEFT JOIN menu m ON im.menu_id = m.id
    LEFT JOIN tours_galeria g ON t.id = g.tour_id
    WHERE t.slug = ?
");
    
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    if (count($rows) === 0) {
        return null;
    }

    // Datos base del tour
    $tourData = $rows[0];
    $tourId = $tourData['id']; 
    
    // IMPORTANTE: Aquí ahora tienes $tourData['tipo_slug'] que viene de la tabla menu
    
    // Limpieza para el objeto JSON
    unset($tourData['galeria_id'], $tourData['imagen_galeria'], $tourData['url']);
    
    $galeria = [];
    foreach ($rows as $row) {
        if (!is_null($row['galeria_id'])) {
            $galeria[] = [
                'id'     => $row['id_galeria'] ?? $row['galeria_id'],
                'nombre' => $row['imagen_galeria'],
                'url'    => $row['url']
            ];
        }
    }

    // Consulta de FAQs (se mantiene igual)
    $preguntas = [];
    $stmtPreguntas = $conn->prepare("
        SELECT question as pregunta, answer as respuesta, display_order as orden 
        FROM tour_faqs 
        WHERE tour_id = ? 
        ORDER BY orden ASC
    ");
    $stmtPreguntas->bind_param("i", $tourId);
    $stmtPreguntas->execute();
    $resPreg = $stmtPreguntas->get_result();

    while ($p = $resPreg->fetch_assoc()) {
        $preguntas[] = $p;
    }
    $stmtPreguntas->close();

    return array_merge($tourData, [
        'galeria' => array_values(array_unique($galeria, SORT_REGULAR)),
        'preguntas' => $preguntas
    ]);
}

if (isset($_GET['slug'])) {
    $slug  = $_GET['slug'];
    $data = getTour($slug);
    
    if ($data) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Tour no encontrado"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros"]);
}
?>