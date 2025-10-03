<?php
// public/index.php
header('Content-Type: application/json; charset=utf-8');

// จัดการ CORS / Preflight
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ApplianceController.php';
require_once __DIR__ . '/../src/Response.php';

$db = (new Database())->pdo();
$controller = new ApplianceController($db);

// path เช่น /api/appliances หรือ /api/appliances/3
$uri  = strtok($_SERVER['REQUEST_URI'], '?');               // ตัด query string
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');       // /appliances_api/public
$path = '/'.trim(str_replace($base, '', $uri), '/');        // เริ่มด้วย '/'

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
}

try {
    if (preg_match('#^/api/appliances/?$#', $path)) {
        if ($method === 'GET')    { $controller->index(); }
        if ($method === 'POST')   { $controller->create(); }
        Response::badRequest('Unsupported method on collection');
    } elseif (preg_match('#^/api/appliances/(\d+)$#', $path, $m)) {
        $id = (int)$m[1];
        if ($method === 'GET')                  { $controller->show($id); }
        if ($method === 'PUT' || $method === 'PATCH') { $controller->update($id); }
        if ($method === 'DELETE')               { $controller->destroy($id); }
        Response::badRequest('Unsupported method on resource');
    } else {
        Response::json(['error' => 'Route not found', 'path' => $path], 404);
    }
} catch (Throwable $e) {
    Response::serverError($e->getMessage());
}
