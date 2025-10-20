<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Crear conexión PDO local sin tocar con_db.php
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
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error conexión PDO: ' . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['codigo'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos: faltan id o codigo']);
    exit;
}

$id = intval($data['id']);
$codigo = trim($data['codigo']);

try {
    $stmt = $pdo->prepare("UPDATE dispatch SET codigo_despacho = :codigo WHERE id = :id");
    $stmt->execute(['codigo' => $codigo, 'id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Código de despacho actualizado']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error PDO: ' . $e->getMessage()]);
}
