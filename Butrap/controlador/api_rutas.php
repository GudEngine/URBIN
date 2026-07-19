<?php
/* API RESTful para la asignación y planificación de rutas
 */

require_once 'config.php';
require_once 'ruta.php'; 

$rutaObj = new Ruta($conn);
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';
header('Content-Type: application/json');

switch ($method) {
    case 'GET':
       if ($endpoint === '/rutas') {
            // Obtiene todos los contenedores de la base de datos
            $rutas = $rutaObj->getAllRutas();
            echo json_encode($rutas);
            exit;
        }//bitacora del integrante: Mensaje para el futuro yo:
        //no sé si lo precisaré pero animate algún día a getearme una ruta por ID
    case 'POST':
        if ($endpoint === '/rutas') {
            // Lee el JSON entrante
            $data = json_decode(file_get_contents('php://input'), true);
            
            $rutaObj->addRuta($data);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["mensaje" => "Método no permitido."]);
        break;
}