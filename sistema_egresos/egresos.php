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

class EgresosHandler
{



    private $conexion;

    public function __construct()
    {
        global $conexion; // Utiliza la instancia de la conexión definida en conexion.php
        $this->conexion = $conexion;
    }

    public function create($request)
    {

        $usuario_id = $request["usuario_id"];
        $descripcion = $request["descripcion"];
        $monto = $request["monto"];

        try {

            $stmt = $this->conexion->prepare("INSERT
         INTO egresos (usuario_id, descripcion, monto) 
         VALUES (:usuario_id, :descripcion, :monto)");

            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':monto', $monto);
            $stmt->execute();

            return "Egreso creado exitosamente";

            $this->conexion = null;
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Error al insertar el registro: " . $e->getMessage();
        }
    }



    public function list()
    {
        $sql = "SELECT egreso.*, 
        usuario.nombres as usuario 
        FROM egresos egreso 
        INNER JOIN usuarios usuario
        ON usuario.id = egreso.usuario_id ORDER BY egreso.id DESC";
        $query = $this->conexion->prepare($sql);

        $query->execute();

        $results = $query->fetchAll(PDO::FETCH_OBJ);

        $this->conexion = null;

        return $results;
    }


   


    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':

                $result = $this->list();
                echo json_encode($result);

                break;

            case 'POST':

                $json_data = file_get_contents('php://input');
                $data = json_decode($json_data, true);

                $response = $this->create($data);
                echo json_encode(["message" => $response]);

                return;
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
$EgresosHandler = new EgresosHandler();
$EgresosHandler->handleRequest();
