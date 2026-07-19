<?php
/* API RESTful para gestionar contenedores de residuos
 * Por ahora permite operaciones  de registro y lectura
 * Requiere conexión a una base de datos MySQL
 */

// Importa las dependencias necesarias
require_once 'config.php';
require_once 'contenedor.php'; // 

// Crea la instancia de la clase Contenedor
$contenedorObj = new Contenedor($conn);

// Obtiene el método de la solicitud HTTP y el endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';
// Establece el tipo de contenido de la respuesta (json)
header('Content-Type: application/json');

// Procesa la solicitud según el método HTTP
switch ($method) {
    case 'GET':
       if ($endpoint === '/contenedores') {
            // Obtiene todos los contenedores de la base de datos
            $contenedores = $contenedorObj->getAllContenedores();
            echo json_encode($contenedores);
            exit;
        } elseif (preg_match('/^\/contenedores\/(\d+)$/', $endpoint, $matches)) {
            // Obtiene un contenedor específico por ID pasándole el número capturado en la URL
            $contenedorId = $matches[1];
            $contenedor = $contenedorObj->getContenedorById($contenedorId);
            
            if ($contenedor) {
                echo json_encode($contenedor);
            } else {
                http_response_code(404);
                echo json_encode(["mensaje" => "⚠️ Contenedor no encontrado."]);
            }
            exit;
        }
        break;
    case 'POST':
        if ($endpoint === '/contenedores') {
            // Recibe los datos crudos en formato JSON desde el frontend
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Llama directamente a la función. Ella procesa, valida, 
            // responde con su propio echo y corta la ejecución con exit;
            $contenedorObj->addContenedor($data);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["mensaje" => "Método no permitido en este endpoint municipal."]);
        break;
}