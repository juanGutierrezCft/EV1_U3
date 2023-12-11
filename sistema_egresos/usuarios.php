


<?php

require_once('conexion.php');
// Habilita CORS para permitir solicitudes desde cualquier origen
header("Access-Control-Allow-Origin: *");

// Permite los métodos HTTP que deseas permitir (GET, POST, PUT, DELETE, etc.)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// Permite encabezados personalizados (en este caso, Content-Type)
header("Access-Control-Allow-Headers: Content-Type");

// Configura el tipo de contenido de la respuesta
header("Content-Type: application/json");

class UsuariosHandler
{
    private $conexion;

    public function __construct()
    {
        global $conexion; // Utiliza la instancia de la conexión definida en conexion.php
        $this->conexion = $conexion;
    }

    public function create($request)
    {
        // Verificar si el correo ya está tomado
        $correoExistente = $this->conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :correo");
        $correoExistente->bindParam(':correo', $request['correo']);
        $correoExistente->execute();
        $existe = $correoExistente->fetchColumn();

        if ($existe) {
            // El correo ya está tomado, manejar la situación (por ejemplo, mostrar un mensaje de error)
            return "El correo ya está tomado. Por favor, elige otro.";
        } else {
            // El correo no está tomado, proceder con la inserción
            $stmt = $this->conexion->prepare("INSERT INTO usuarios (nombres, correo, contraseña) VALUES (:nombres, :correo, :contrasena)");
            $stmt->bindParam(':nombres', $request['nombres']);
            $stmt->bindParam(':correo', $request['correo']);
            $stmt->bindParam(':contrasena', $request['contrasena']);

            try {
                $stmt->execute();
                return "Usuario creado exitosamente";
            } catch (PDOException $e) {
                http_response_code(500);
                echo "Error al insertar el registro: " . $e->getMessage();
            }
        }
    }


    public function listarPorUsuario($usuario_id)
    {


        $stmt = $this->conexion->prepare("SELECT * FROM egresos WHERE usuario_id = :usuario_id");
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

       // $this->conexion = null;

        return $results;
    }

    public function list()
    {
        // Selecciona todas las columnas de usuarios y las columnas relevantes de egresos
        $sql = "SELECT * FROM usuarios";

        $query = $this->conexion->prepare($sql);

        $query->execute();

        // Devuelve el resultado como un array de objetos
        $usuarios = $query->fetchAll(PDO::FETCH_OBJ);


        $data = [];

        foreach($usuarios as $usuario){
            
            $egresosUsuario = $this->listarPorUsuario($usuario->id);
            
            $usuario->egresos = $egresosUsuario;

            $data[] = $usuario;
        }

        $this->conexion = null;

        return $data;
    }


    public function login($request)
    {
        $correo = $request["correo"];
        $contraseña = $request["contraseña"];
        if (empty($correo) || empty($contraseña)) {
            return "Correo y contraseña son obligatorios.";
        }
        $sql = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";
        $query = $this->conexion->prepare($sql);
        $query->bindParam(":correo", $correo);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_OBJ);
        if ($user && $user->contraseña === $contraseña) {

            // Usuario autenticado correctamente
            return $user;
        } else {
            http_response_code(401);
            return "Correo o contraseña incorrectos.";
        }
    }



    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':

                $result = $this->list();
                // echo json_encode(["Usuario" =>$result]);
                echo json_encode($result);
                break;

            case 'POST':

                $json_data = file_get_contents('php://input');
                $data = json_decode($json_data, true);
                $accion = $data["accion"];


                if ($accion === "login") {
                    $response = $this->login($data);
                    echo json_encode($response);
                    return;
                } elseif ($accion === "crear") {
                    $response = $this->create($data);
                    echo json_encode(["message" => $response]);
                }

                //$this->create($data);
                break;

            case 'PUT':
                // Manejar solicitud PUT para actualizar un registro existente.
                // Los datos pueden estar en el cuerpo de la solicitud (dependiendo de cómo se envíen).
                $data = json_decode(file_get_contents('php://input'), true);
                // $this->update($resourceId, $data);
                break;

            case 'DELETE':
                // Manejar solicitud DELETE para eliminar un registro existente.
                //$this->delete($resourceId);
                break;

            default:
                // Manejar otros métodos HTTP según sea necesario.
                break;
        }
    }
}

// Uso de la clase CRUDHandler para manejar solicitudes CRUD.
$UsuariosHandler = new UsuariosHandler();
$UsuariosHandler->handleRequest();
?>
