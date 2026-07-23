<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

class Contenedor
{
    private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function getAllContenedores()
    {
        $query = "SELECT * FROM contenedor";
        $result = mysqli_query($this->conn, $query);
        $contenedores = [];
        
        while($row = mysqli_fetch_assoc($result)) {
            $contenedores[] = $row;
        }
        return $contenedores;
    }

    // Obtener un contenedor por su ID (método GET, endpoint: /contenedores/<id>)
    public function getContenedorById($id)
    {
        // Limpiamos espacios
        $id = trim($id);

        // Validación de seguridad básica: que sea numérico
        if (empty($id) || !ctype_digit($id)) {
            return null;
        }

        $id = mysqli_real_escape_string($this->conn, $id);
        
        // Al ser un INT, en la query puede ir sin comillas, igual que la cédula
        $query = "SELECT * FROM contenedor WHERE cont_id = $id";
        $result = mysqli_query($this->conn, $query);
        $contenedor = mysqli_fetch_assoc($result);
        
        return $contenedor;
    }
    // Método para registrar un nuevo contenedor (método POST, endpoint: /contenedores)
    public function addContenedor($data)
    {
        // 1. lo mismo que en usuario
        if (empty($data['cont_id']) || trim($data['cont_id']) === "" || empty($data['cont_calle']) || trim($data['cont_calle']) === "" || empty($data['cont_estado']) || trim($data['cont_estado']) === "") {
            http_response_code(400);
            echo json_encode(["mensaje" => "🙅 Error: Todos los campos son obligatorios y no pueden estar vacíos."]);
            exit;
        }

        // 2. tres cuartos de lo mismo que en usuario
        $cont_id     = trim($data['cont_id']);
        $cont_calle  = trim($data['cont_calle']);
        $cont_estado = trim($data['cont_estado']);

        // 3. Chequear que el ID sea numérico
        if (!ctype_digit($cont_id)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El número identificador del contenedor debe contener únicamente números."]);
            exit;
        }

        // 4. Validar el límite de tamaño de la calle para VARCHAR(29)
        if (strlen($cont_calle) > 29) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: La ubicación de la calle supera el límite máximo permitido de 29 caracteres."]);
            exit;
        }

        // 5. que mande los valores del select municipal, después veo si lo borro añadiendo constraints a sql
        $estadosValidos = ['funcional', 'roto', 'desbordado'];
        if (!in_array($cont_estado, $estadosValidos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El estado ingresado no es válido para el sistema de gestión."]);
            exit;
        }

        // 6. Escapamos los datos para blindar la query
        $cont_id     = mysqli_real_escape_string($this->conn, $cont_id);
        $cont_calle  = mysqli_real_escape_string($this->conn, $cont_calle);
        $cont_estado = mysqli_real_escape_string($this->conn, $cont_estado);

        try {
            // Insertamos todas las columnas en la tabla correspondiente
            $query = "INSERT INTO contenedor (cont_id, cont_calle, cont_estado) 
                      VALUES ($cont_id, '$cont_calle', '$cont_estado')"; // cont_id va sin comillas por ser INT puro
            
            mysqli_query($this->conn, $query);

            http_response_code(201); // 201 = Creado con éxito
            echo json_encode(["mensaje" => "Contenedor registrado con éxito."]);
            exit;

        } catch (mysqli_sql_exception $e) {
            $codigoErrorMySQL = $e->getCode();
            
            // Al igual que con la cédula, el error 1062 significa que el ID ya existe
            if ($codigoErrorMySQL === 1062) {
                http_response_code(400);
                echo json_encode(["mensaje" => "⚠️ Error: El número identificador de contenedor ya se encuentra registrado en el sistema."]);
            } else {
                http_response_code(500); // Falla crítica interna
                echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
            }
            exit;
        }
    }
}