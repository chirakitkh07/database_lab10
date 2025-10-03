<?php
class Response {
    public static function json($data, int $status = 200, array $extraHeaders = []) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        foreach ($extraHeaders as $k => $v) {
            header("$k: $v");
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    public static function ok($data) { self::json(['data' => $data], 200); }
    public static function created($data) { self::json(['message' => 'Created', 'data' => $data], 201); }
    public static function updated($data) { self::json(['message' => 'Updated', 'data' => $data], 200); }
    public static function deleted() { self::json(['message' => 'Deleted'], 200); }
    public static function notFound() { self::json(['error' => 'Not found'], 404); }
    public static function badRequest($details = null) { self::json(['error' => 'Bad Request', 'details' => $details], 400); }
    public static function conflict($details = null) { self::json(['error' => 'Conflict', 'details' => $details], 409); }
    public static function serverError($details = null) { self::json(['error' => 'Server Error', 'details' => $details], 500); }
}