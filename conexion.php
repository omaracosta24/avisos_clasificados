<?php
$host = 'localhost';
$db   = 'sistema_avisos';
$user = 'root';
$pass = 'root'; 
$charset = 'utf8mb4'; 

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    
    $pdo = new PDO($dsn, $user, $pass);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "¡El puente funciona! Conectado con éxito a sistema_avisos.";
    
} catch (PDOException $e) {
    die("Error crítico: No se pudo conectar a la base de datos. Detalle: " . $e->getMessage());
}
?>