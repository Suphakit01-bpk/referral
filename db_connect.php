<?php
header('Content-Type: application/json');

$host = '172.29.10.98';
$port = '5432';
$dbname = 'referral';
$user = 'postgres';
$password = 'BPK9@support'; // Change this to your PostgreSQL password

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET client_encoding TO 'UTF8'");
} catch(PDOException $e) {
    die(json_encode(['error' => 'Connection failed: ' . $e->getMessage()]));
}
?>
