<?php
/* Clase usuario para gestionar con API RESTful
 * Permite operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * Requiere conexión a una 
 * ase de datos MySQL
 */

// Configuracion del reporte de errores
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

class Usuario
{
	private $conn;

	// Constructor que recibe la conexión a la base de datos
	public function __construct($conn)
	{
		$this->conn = $conn;
	}

	// Métodos para manejar usuarios
	// Obtener todos los usuarios (método GET, endopoint: /usuarios)
	public function getAllUsuarios()
	{
		$query = "SELECT * FROM usuario";
		$result = mysqli_query($this->conn, $query);
		$usuarios = [];
		while($row = mysqli_fetch_assoc($result)) {
			$usuarios[] = $row;
		}
		return $usuarios;
	}
	// Obtener un usuario por ID  (método GET, endopoint: /usuarios/<id>)
	public function getUsuarioById($cedula){
		$query = "SELECT * FROM usuario WHERE usr_ci = $cedula ";
		$result = mysqli_query($this->conn, $query);
		$usuario = mysqli_fetch_assoc($result);
		return $usuario;
	}
	//asesinar una cuenta por e-mail
	public function deleteUsuarioByCI($cedula){
		
    $cedula = trim($cedula);

    // 2. checando que no me llegue vacía
    if (empty($cedula) || !ctype_digit($cedula)) {
        http_response_code(400); 
        echo json_encode(["mensaje" => "⚠️ Error: La cédula ingresada no es válida. Asegúrese de ingresar solo números sin espacios."]);
        exit;
    }

    // refuerzo a la seguridad
    $cedula = mysqli_real_escape_string($this->conn, $cedula);
		try {
			$query = "DELETE FROM usuario WHERE usr_ci = '$cedula'";
			mysqli_query($this->conn, $query);

			//me gusta mucho esto de affected_rows
			if (mysqli_affected_rows($this->conn) > 0) {
				http_response_code(200); // 200 significa É.X.I.T.O
				echo json_encode((["mensaje" => "Funcionario eliminado con éxito"]));
				exit;
			} else {
            // El query funcionó pero la cédula no existía en la tabla
            http_response_code(400); 
            echo json_encode(["mensaje" => "⚠️ Error: Cédula no registrada."]);
			exit;
        }
        

		} catch (mysqli_sql_exception $e) {
			http_response_code(500); 
			echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
			exit;
		}
		 
	}
	// Agregar un nuevo usuario (método POST, endopoint: /usuarios)
	public function addUsuario($data){
    // 1. Verificación básica de existencia de llaves
		if (empty($data['usr_ci']) || trim($data['usr_ci']) === "" || empty($data['usr_name']) || trim($data['usr_name']) === "" ||	empty($data['usr_email']) || trim($data['usr_email']) === "" ||	empty($data['usr_rol']) || trim($data['usr_rol']) === "" ||	empty($data['usr_telefono']) || trim($data['usr_telefono']) === "")	{
			http_response_code(400);
			echo json_encode(["mensaje" => "🙅 Error: Todos los campos son obligatorios y no pueden estar vacíos."]);
			exit;
		}

		// 2 sacamos los espacios con trim
		$usr_ci       = trim($data['usr_ci']);
		$usr_name     = trim($data['usr_name']);
		$usr_email    = trim($data['usr_email']);
		$usr_rol      = trim($data['usr_rol']);
		$usr_telefono = trim($data['usr_telefono']);

		

		// 3 Checo que  la cédula y el teléfono anden bien
		if (!ctype_digit($usr_ci) || strlen($usr_ci) !== 8 || !ctype_digit($usr_telefono) || strlen($usr_telefono) !== 8) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: La Cédula de Identidad y el teléfono deben contener únicamente 8 números, sin espacios."]);
			exit;
		}

		// 3.5 que el nombre no tenga números
		if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $usr_name)) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El nombre solo puede incluir letras y espacios."]);
			exit;
		}
		// 4. Escapamos a una vida feliz con datos para la query una vez validados
		$usr_ci       = mysqli_real_escape_string($this->conn, $usr_ci);
		$usr_name     = mysqli_real_escape_string($this->conn, $usr_name);
		$usr_email    = mysqli_real_escape_string($this->conn, $usr_email);
		$usr_rol      = mysqli_real_escape_string($this->conn, $usr_rol);
		$usr_telefono = mysqli_real_escape_string($this->conn, $usr_telefono);

		try {
			$query = "INSERT INTO usuario (usr_ci, usr_name, usr_email, usr_rol, usr_telefono) 
					VALUES ('$usr_ci', '$usr_name', '$usr_email', '$usr_rol', '$usr_telefono')";
			
			mysqli_query($this->conn, $query);

			http_response_code(201); // 201 = Creado con éxito
			echo json_encode(["mensaje" => "Usuario registrado con éxito."]);
			exit;

		} catch (mysqli_sql_exception $e) {
			$codigoErrorMySQL = $e->getCode();
			//después de tanto validar, la cédula ya registrada debería ser el único error del usuario 
			if ($codigoErrorMySQL === 1062) {
				http_response_code(400);
				echo json_encode(["mensaje" => "⚠️ Error: La Cédula de Identidad ya se encuentra registrada en el sistema."]);
			} else {
				http_response_code(500); // Algo desastrozo ocurrió con el servidor
				echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
			}
			exit;
		}
	}
	public function modificarUsuario($data){
		if (empty($data['usr_ci']) || trim($data['usr_ci']) === "" || empty($data['usr_name']) || trim($data['usr_name']) === "" ||	empty($data['usr_email']) || trim($data['usr_email']) === "" ||	empty($data['usr_rol']) || trim($data['usr_rol']) === "" ||	empty($data['usr_telefono']) || trim($data['usr_telefono']) === "")	{
			http_response_code(400);
			echo json_encode(["mensaje" => "🙅 Error: Todos los campos son obligatorios y no pueden estar vacíos."]);
			exit;
		}

		// 2 sacamos los espacios con trim
		$usr_ci       = trim($data['usr_ci']);
		$usr_name     = trim($data['usr_name']);
		$usr_email    = trim($data['usr_email']);
		$usr_rol      = trim($data['usr_rol']);
		$usr_telefono = trim($data['usr_telefono']);

		

		// 3 Checo que  la cédula y el teléfono anden bien
		if (!ctype_digit($usr_ci) || strlen($usr_ci) !== 8 || !ctype_digit($usr_telefono) || strlen($usr_telefono) !== 8) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: La Cédula de Identidad y el teléfono deben contener únicamente 8 números, sin espacios."]);
			exit;
		}

		// 3.5 que el nombre no tenga números
		if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $usr_name)) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El nombre solo puede incluir letras y espacios."]);
			exit;
		}
		// 4. Escapamos a una vida feliz con datos para la query una vez validados
		$usr_ci       = mysqli_real_escape_string($this->conn, $usr_ci);
		$usr_name     = mysqli_real_escape_string($this->conn, $usr_name);
		$usr_email    = mysqli_real_escape_string($this->conn, $usr_email);
		$usr_rol      = mysqli_real_escape_string($this->conn, $usr_rol);
		$usr_telefono = mysqli_real_escape_string($this->conn, $usr_telefono);

		try{
			$query = "UPDATE usuario SET 
                usr_name = '$usr_name', 
                usr_email = '$usr_email', 
                usr_rol = '$usr_rol', 
                usr_telefono = '$usr_telefono' 
                WHERE usr_ci = '$usr_ci'";
        
			mysqli_query($this->conn, $query);

			// ¡Acá manejamos el resultado con affected_rows!
			if (mysqli_affected_rows($this->conn) > 0) {
				http_response_code(200);
				echo json_encode(["mensaje" => "Usuario actualizado con éxito."]);
				exit;
			} else {
				// Si dio 0, puede ser porque la cédula no existe o porque guardó los mismos datos sin cambiar nada.
				// para no complicarme le digo que ambas cosas son un error
				http_response_code(400);
				echo json_encode(["mensaje" => "⚠️ Error: No se realizaron cambios (Cédula no registrada o los datos ingresados son idénticos a los actuales)."]);
				exit;
			}
		} catch (mysqli_sql_exception $e) {
		//	$codigoErrorMySQL = $e->getCode();
	
			/*if ($codigoErrorMySQL === 1062) {
				http_response_code(400);
				echo json_encode(["mensaje" => "⚠️ Error: El correo electrónico ya se encuentra registrado por otro usuario."]);
			} else {*/
				// felizmente, creo que no hay errores de usuario que caigan acá
				http_response_code(500);
				echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
		//	}
			exit;
			//lo demás lo dejo por si ponemos correo como unique
		}
	}

	// Iniciar sesión de usuario (método POST, endopoint: /login)
	public function loginUsuario($data){
		if(!isset($data['usr_email']) || !isset($data['usr_pass'])) {
			http_response_code(400);
			return json_encode(["error" => "Datos incompletos"]);
		}else{
			$usr_email = $data['usr_email'];
			$usr_pass = $data['usr_pass'];
			$query = "SELECT * FROM usuario WHERE usr_email = '$usr_email'";
			$result = mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result) > 0){
				$usuario = mysqli_fetch_assoc($result);
				if(password_verify($usr_pass, $usuario['usr_pass'])){
					$result = mysqli_query($this->conn, $query);
					http_response_code(200);
					return json_encode(["success" => [$usuario['usr_name'], $usr_email, $usuario['ID'] ]])	;

				} else {
					http_response_code(400);
					return json_encode(["error" => "Contraseña incorrecta"]);
				}
			} else {
				http_response_code(400);
				return json_encode(["error" => "Usuario no encontrado"]);
			}
		}
	}
}