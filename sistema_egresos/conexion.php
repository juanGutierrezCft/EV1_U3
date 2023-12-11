<?php


$servidor = "localhost";
$usuario = "root";
$password = "";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=sistema_egresos", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
}
