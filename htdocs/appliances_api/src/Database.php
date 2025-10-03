<?php
// src/Database.php
class Database {
    private $pdo;

    public function __construct() {
        // ปรับค่าตามเครื่องของคุณ
        $host = '127.0.0.1';
        $db   = 'webapi_demo';
        $user = 'chirakit';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // assoc array
            PDO::ATTR_EMULATE_PREPARES   => false,                  // ใช้ native prepares
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Database connection failed', 'details' => $e->getMessage()]);
            exit;
        }
    }

    public function pdo(): PDO {
        return $this->pdo;
    }
}
