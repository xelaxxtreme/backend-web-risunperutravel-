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
            SELECT id, slug, nombre, tipo, departamento
            FROM tours
            WHERE idioma = ?
            ORDER BY id ASC
        ");
        $stmt->bind_param("s", $lang);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();

        // Agrupamos
        $agrupadoRaw = [];
        foreach ($data as $item) {
            $tipo = strtolower(trim($item['tipo']));
            $departamento = trim($item['departamento']);

            $nuevoItem = [
                'id'          => $item['id'],
                'nombre'      => $item['nombre'],
                'departamento'=> $departamento,
                'tipo'        => $item['tipo'],
                'url'         => '/' . $item['slug']
            ];

            if ($tipo === 'tours') {
                if (!isset($agrupadoRaw[$tipo])) {
                    $agrupadoRaw[$tipo] = [];
                }
                $grupoIndex = null;
                foreach ($agrupadoRaw[$tipo] as $index => $grupo) {
                    if ($grupo['departamento'] === $departamento) {
                        $grupoIndex = $index;
                        break;
                    }
                }
                if ($grupoIndex === null) {
                    $agrupadoRaw[$tipo][] = [
                        'departamento' => $departamento,
                        'items' => []
                    ];
                    $grupoIndex = count($agrupadoRaw[$tipo]) - 1;
                }
                $agrupadoRaw[$tipo][$grupoIndex]['items'][] = $nuevoItem;
            } else {
                if (!isset($agrupadoRaw[$tipo])) {
                    $agrupadoRaw[$tipo] = [];
                }
                $agrupadoRaw[$tipo][] = $nuevoItem;
            }
        }

        // Formato final
        $agrupadoFinal = [];
        foreach ($agrupadoRaw as $tipo => $items) {
            $agrupadoFinal[] = [
                'nombre' => $tipo,
                'ruta'   => '/' . $tipo,
                'items'  => $items
            ];
        }

        return $agrupadoFinal;
    } catch (Exception $e) {
        error_log("Error al obtener el menú: " . $e->getMessage());
        return ["error" => "Error interno del servidor"];
    }
}

// 🚀 Aquí solo un echo
if (isset($_GET['lang'])) {
    $lang  = $_GET['lang'];
    $data = getItemsMenu($lang);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "Faltan parámetros"]);
}