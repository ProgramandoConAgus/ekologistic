<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Conexión PDO
$host = 'localhost';
$db   = 'u981249563_ekologisticaaa';
$user = 'u981249563_agustinapontee';
$pass = 'Pca@70071';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Consulta para traer códigos únicos desde palets_cargados
    $stmt = $pdo->query("SELECT DISTINCT codigo_despacho FROM palets_cargados WHERE codigo_despacho IS NOT NULL AND codigo_despacho != ''");

    $codigos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'codigos' => $codigos
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error PDO: ' . $e->getMessage()
    ]);
}
