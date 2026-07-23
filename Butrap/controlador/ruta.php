<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

class Ruta
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    //traer todas las rutas de lahistoria, algún día si lo veo preciso, cambiaré esto por rutas de
    //la semana o algo así
    public function getAllRutas(){
        // Traemos los datos cruzando la cabecera (ruta) con el detalle (ruta_contenedor)
        $query = "SELECT r.ruta_id, r.ruta_fecha, r.ruta_camion, rc.cont_id, rc.vaciado 
                FROM ruta r
                INNER JOIN ruta_contenedor rc ON r.ruta_id = rc.ruta_id AND r.ruta_fecha = rc.ruta_fecha
                ORDER BY r.ruta_fecha DESC, r.ruta_id ASC";
                    
        $result = mysqli_query($this->conn, $query);
        
        // Si la consulta falla por alguna razón externa, evitamos un crash devolviendo un array vacío
        if (!$result) {
            return [];
        }
        
        $rutasAgrupadas = [];
        
        // Vamos asignándole a $row el valor de cada fila devuelta por MySQL
        while ($row = mysqli_fetch_assoc($result)) {
            // Mantenemos tu genial idea de usar la clave combinada [fecha]_[id_ruta]
            $claveUnica = $row['ruta_fecha'] . '_' . $row['ruta_id'];

            // Si es la primera vez que vemos esta combinación en el bucle, inicializamos la columna de la ruta
            if (!isset($rutasAgrupadas[$claveUnica])) {
                $rutasAgrupadas[$claveUnica] = [
                    "ruta_id"      => intval($row['ruta_id']),
                    "ruta_fecha"   => $row['ruta_fecha'],
                    "ruta_camion"  => $row['ruta_camion'],
                    "contenedores" => [] // Espacio listo para inyectar los contenedores relacinados
                ];
            }

            // Agregamos el contenedor actual con su estado de vaciado al listado de esta ruta específica
            $rutasAgrupadas[$claveUnica]["contenedores"][] = [
                "cont_id"      => intval($row['cont_id']),
                // boolean real de JS
                "cont_vaciado" => (bool)$row['vaciado'] 
            ];
        }

        // Quitamos las claves temporales (ej: '2026-07-19_10') y devolvemos la lista limpia e indexada
        return array_values($rutasAgrupadas);
    }

    // Registrar una nueva ruta asignando contenedores (POST)
    public function addRuta($data) {
    // 1. ESCUDO NIVEL 1: Verificación de campos obligatorios
        if (
            empty($data['ruta_fecha']) || trim($data['ruta_fecha']) === "" ||
            empty($data['ruta_camion']) || trim($data['ruta_camion']) === "" ||
            empty($data['ruta_id']) || 
            empty($data['contenedores'])
        ) {
            http_response_code(400);
            echo json_encode(["mensaje" => "🙅 Error: Fecha, camión, número de ruta y contenedores son obligatorios."]);
            exit;
        }

        // Limpieza e interpretación de tipos de datos
        $ruta_fecha       = trim($data['ruta_fecha']);
        $ruta_camion      = strtoupper(str_replace(' ', '', trim($data['ruta_camion'])));
        $ruta_id          = intval($data['ruta_id']);
        $ids_contenedores = $data['contenedores']; 

        // 2. ESCUDO NIVEL 2: Validaciones lógicas
        if ($ruta_id <= 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El ID de la ruta debe ser un número positivo mayor a cero."]);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ruta_fecha)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El formato de la fecha proporcionada no es válido."]);
            exit;
        }
        //a mi también me gustaría haber planificado mi pasado 
        date_default_timezone_set('America/Montevideo');
        $hoy = date('Y-m-d');
        if (strtotime($ruta_fecha) < strtotime($hoy)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: No se pueden planificar rutas para fechas pasadas."]);
            exit;
        }

        if (!preg_match('/^[A-Z]{3}\d{4}$/', $ruta_camion)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El formato de la matrícula del camión es inválido (deben ser 3 letras y 4 números)."]);
            exit;
        }

        if (!is_array($ids_contenedores) || count($ids_contenedores) === 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: Debe seleccionar al menos un contenedor para registrar la ruta."]);
            exit;
        }

        // CHEQUEO 1: Verificar que el camión exista
        $stmt_camion = mysqli_prepare($this->conn, "SELECT cam_matricula FROM camion WHERE cam_matricula = ?");
        mysqli_stmt_bind_param($stmt_camion, "s", $ruta_camion);
        mysqli_stmt_execute($stmt_camion);
        mysqli_stmt_store_result($stmt_camion);
        if (mysqli_stmt_num_rows($stmt_camion) === 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El camión con matrícula '$ruta_camion' no existe."]);
            mysqli_stmt_close($stmt_camion);
            exit;
        }
        mysqli_stmt_close($stmt_camion);

        // CHEQUEOS DE CONTENEDORES
        foreach ($ids_contenedores as $id_cont) {
            $id_cont = intval($id_cont);
            if ($id_cont <= 0) {
                http_response_code(400);
                echo json_encode(["mensaje" => "⚠️ Error: Se detectó un ID de contenedor inválido."]);
                exit;
            }

            // CHEQUEO 2: Verificar que el contenedor exista en el sistema
            $stmt_check = mysqli_prepare($this->conn, "SELECT cont_id FROM contenedor WHERE cont_id = ?");
            mysqli_stmt_bind_param($stmt_check, "i", $id_cont);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            
            if (mysqli_stmt_num_rows($stmt_check) === 0) {
                http_response_code(400);
                echo json_encode(["mensaje" => "⚠️ Error: El contenedor con ID $id_cont no existe en el sistema."]);
                mysqli_stmt_close($stmt_check);
                exit;
            }
            mysqli_stmt_close($stmt_check);

            //CHEQUEO 3: Validar que el contenedor NO esté asignado a OTRA ruta en la misma fecha
            $sql_fecha = "SELECT rc.ruta_id 
                        FROM ruta_contenedor rc
                        INNER JOIN ruta r ON rc.ruta_id = r.ruta_id
                        WHERE rc.cont_id = ? AND r.ruta_fecha = ?";
            
            $stmt_fecha = mysqli_prepare($this->conn, $sql_fecha);
            mysqli_stmt_bind_param($stmt_fecha, "is", $id_cont, $ruta_fecha);
            mysqli_stmt_execute($stmt_fecha);
            mysqli_stmt_store_result($stmt_fecha);

            if (mysqli_stmt_num_rows($stmt_fecha) > 0) {
                http_response_code(400);
                echo json_encode(["mensaje" => "⚠️ Error: El contenedor con ID $id_cont ya está asignado a otra ruta para el día $ruta_fecha."]);
                mysqli_stmt_close($stmt_fecha);
                exit;
            }
            mysqli_stmt_close($stmt_fecha);
        }

        // Escapamos strings antes de las consultas directas de la transacción
        $ruta_fecha  = mysqli_real_escape_string($this->conn, $ruta_fecha);
        $ruta_camion = mysqli_real_escape_string($this->conn, $ruta_camion);

        // 3. PROCESAMIENTO SEGURO (Transacción)
        mysqli_begin_transaction($this->conn);

        try {
            // PASO A: Insertar la cabecera en 'ruta' (Mantiene sus columnas)
            $query_ruta = "INSERT INTO ruta (ruta_id, ruta_fecha, ruta_camion) 
                        VALUES ($ruta_id, '$ruta_fecha', '$ruta_camion')";
            mysqli_query($this->conn, $query_ruta);

            // PASO B: Asociar contenedores incluyendo la 'ruta_fecha' en la tabla intermedia
            foreach ($ids_contenedores as $id_cont) {
                $id_cont = intval($id_cont);

                // Ahora añadimos '$ruta_fecha' en la query de inserción de la intermedia
                $query_relacion = "INSERT INTO ruta_contenedor (ruta_id, ruta_fecha, cont_id, vaciado, volumen_cargado) 
                                VALUES ($ruta_id, '$ruta_fecha', $id_cont, false, 0)";
                
                mysqli_query($this->conn, $query_relacion);
            }

            mysqli_commit($this->conn);
            
            http_response_code(201);
            echo json_encode(["mensaje" => "✅ Ruta {$ruta_id} planificada con éxito para los contenedores seleccionados."]);
            exit;

        } catch (mysqli_sql_exception $e) {
            mysqli_rollback($this->conn);
            $codigoError = $e->getCode();
            
            if ($codigoError === 1062) {
                http_response_code(400);
                // Este mensaje ahora está respaldado tanto por el backend como por el motor físico de la BD
                echo json_encode(["mensaje" => "⚠️ Error: El ID de ruta ya existe, o uno de los contenedores seleccionados ya está ocupado en esa misma fecha."]);
            } else {
                http_response_code(500);
                echo json_encode(["mensaje" => "Error interno al procesar la ruta: " . $e->getMessage()]);
            }
            exit;
        }
    }
}