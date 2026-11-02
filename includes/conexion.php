<?php
// =====================================================================
// CONEXIÓN A MYSQL
// Recibe:  los datos de includes/config.php
// Entrega: la variable $pdo, con la que todos los archivos hacen consultas
// =====================================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/http/respuesta.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // si una consulta falla, lanza error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // las filas llegan con nombre de columna
        ]
    );
} catch (PDOException $e) {
    responderError(500, 'No se pudo conectar a la base de datos.');
}
