<?php
/* API RESTful para gestionar los camiones de recolección
 * Permite operaciones de registro (POST) y lectura (GET)
 * Requiere conexión a la base de datos MySQL
 */

// Importa las dependencias necesarias
require_once 'config.php';
require_once 'camion.php'; 

// Crea la instancia de la clase Camion
$camionObj = new Camion($conn);

// Obtiene el método de la solicitud HTTP y el endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';

// Establece el tipo de contenido de la respuesta (json)
header('Content-Type: application/json');

// Procesa la solicitud según el método HTTP
switch ($method) {
    case 'GET':
        if ($endpoint === '/camiones') {
            // Obtiene todos los camiones de la base de datos
            $camiones = $camionObj->getAllCamiones();
            echo json_encode($camiones);
            exit;
        } elseif (preg_match('/^\/camiones\/([a-zA-Z]{3}\d{4})$/', $endpoint, $matches)) {
            // Obtiene un camión específico capturando la matrícula de la URL (ej: /camiones/SAB1234)
            $matricula = $matches[1];
            $camion = $camionObj->getCamionByMatricula($matricula);
            
            if ($camion) {
                echo json_encode($camion);
            } else {
                http_response_code(404);
                echo json_encode(["mensaje" => "⚠️ Vehículo no encontrado."]);
            }
            exit;
        }
        break;

    case 'POST':
        if ($endpoint === '/camiones') {
            // Recibe los datos  en formato JSON desde el formulario del frontend
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Llama a la función de la clase, que valida y guarda
            $camionObj->addCamion($data);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["mensaje" => "Método no permitido en este endpoint de flota."]);
        break;
}