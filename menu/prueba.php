<?php
require_once '../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

function getItemsMenu($lang) {
    global $conn;

    try {
        $stmt = $conn->prepare("
            SELECT t.id, t.nombre, t.departamento, t.slug as ruta, im.nombre_traducido as tipo, m.posicion, m.menu_slug as slug, m.submenu FROM tours t INNER JOIN idioma_menu im ON im.id = t.idTipo INNER JOIN menu m ON m.id = im.menu_id WHERE idioma = ? ORDER BY posicion, nombre ASC;
        ");
        $stmt->bind_param("s", $lang);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
    $tipo = $row['tipo'];
    $slug = $row['slug'];
    $submenu = $row['submenu'];
    $departamento = $row['departamento'];

    // Si aún no existe el grupo por tipo, lo creamos
    if (!isset($data[$tipo])) {
        $data[$tipo] = [
            "nombre" => $tipo,
            "ruta" => "/" . $slug,
            "submenu" => $submenu,
            "items" => []
        ];
    }

    // Si submenu = 1, agrupamos por departamento
    if ($submenu == 1) {
    // Buscar si ya existe un objeto para ese departamento
    $found = false;
    foreach ($data[$tipo]['items'] as &$deptGroup) {
        if ($deptGroup['departamento'] === $departamento) {
            $deptGroup['items'][] = [
                "id" => $row['id'],
                "nombre" => $row['nombre'],
                "departamento" => $departamento,
                "tipo" => $slug,
                "url" => "/" . $row['ruta']
            ];
            $found = true;
            break;
        }
    }
    unset($deptGroup);

    // Si no existe, lo creamos como nuevo objeto dentro del arreglo
    if (!$found) {
        $data[$tipo]['items'][] = [
            "departamento" => $departamento,
            "items" => [[
                "id" => $row['id'],
                "nombre" => $row['nombre'],
                "departamento" => $departamento,
                "tipo" => $slug,
                "url" => "/" . $row['ruta']
            ]]
        ];
    }
}
 else {
        // Caso normal: solo agregamos al array de items
        $data[$tipo]['items'][] = [
            "id" => $row['id'],
            "nombre" => $row['nombre'],
            "departamento" => $departamento,
            "tipo" => $slug,
            "url" => "/" . $row['ruta']
        ];
    }
}


    $output = array_values($data);


        return $output;
    } catch (Exception $e) {
        error_log("Error al obtener el menú: " . $e->getMessage());
        return ["error" => "Error interno del servidor"];
    }
}

if (isset($_GET['lang'])) {
    $lang  = $_GET['lang'];
    $data = getItemsMenu($lang);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "Faltan parámetros"]);
}