<?php
// src/ApplianceController.php
require_once __DIR__ . '/Response.php';

class ApplianceController {
    private PDO $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    /* ---------- Validation ---------- */
    private function validatePayload(array $input, bool $isCreate = true): array {
        $errors = [];

        // required fields on create
        $required = ['sku','name','brand','category','price','stock','warranty_months'];
        if ($isCreate) {
            foreach ($required as $f) {
                if (!array_key_exists($f, $input)) $errors[$f] = 'required';
            }
        }

        // types & rules (check only if provided)
        if (isset($input['sku']) && (!is_string($input['sku']) || $input['sku'] === '')) $errors['sku'] = 'must be non-empty string';
        if (isset($input['name']) && (!is_string($input['name']) || $input['name'] === '')) $errors['name'] = 'must be non-empty string';
        if (isset($input['brand']) && (!is_string($input['brand']) || $input['brand'] === '')) $errors['brand'] = 'must be non-empty string';
        if (isset($input['category']) && (!is_string($input['category']) || $input['category'] === '')) $errors['category'] = 'must be non-empty string';

        if (isset($input['price']) && (!is_numeric($input['price']) || $input['price'] < 0)) $errors['price'] = 'must be >= 0';
        if (isset($input['stock']) && (!is_numeric($input['stock']) || $input['stock'] < 0)) $errors['stock'] = 'must be >= 0';
        if (isset($input['warranty_months']) && (!is_numeric($input['warranty_months']) || $input['warranty_months'] < 0)) $errors['warranty_months'] = 'must be >= 0';

        if (isset($input['energy_rating'])) {
            if ($input['energy_rating'] !== null && (!is_numeric($input['energy_rating']) || $input['energy_rating'] < 1 || $input['energy_rating'] > 5)) {
                $errors['energy_rating'] = 'must be 1-5 or null';
            }
        }

        return $errors;
    }

    /* ---------- Helpers ---------- */
    private function readJsonBody(): array {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            Response::badRequest('Invalid JSON');
        }
        return $data ?? [];
    }

    /* ---------- Endpoints ---------- */

    // GET /api/appliances
    public function index(): void {
        $params = [
            'category'   => $_GET['category']   ?? null,
            'brand'      => $_GET['brand']      ?? null,
            'q'          => $_GET['q']          ?? null, // search in name/sku
            'min_price'  => $_GET['min_price']  ?? null,
            'max_price'  => $_GET['max_price']  ?? null,
            'min_stock'  => $_GET['min_stock']  ?? null,
            'max_stock'  => $_GET['max_stock']  ?? null,
            'energy'     => $_GET['energy']     ?? null,
            'sort'       => $_GET['sort']       ?? 'created_desc',
            'page'       => max(1, (int)($_GET['page'] ?? 1)),
            'per_page'   => min(100, max(1, (int)($_GET['per_page'] ?? 10))),
        ];

        $where = [];
        $bind  = [];

        if ($params['category']) { $where[] = 'category = :category'; $bind[':category'] = $params['category']; }
        if ($params['brand'])    { $where[] = 'brand = :brand';       $bind[':brand']    = $params['brand']; }
        if ($params['q'])        { $where[] = '(name LIKE :q OR sku LIKE :q)'; $bind[':q'] = '%'.$params['q'].'%'; }
        if ($params['min_price'] !== null) { $where[] = 'price >= :min_price'; $bind[':min_price'] = (float)$params['min_price']; }
        if ($params['max_price'] !== null) { $where[] = 'price <= :max_price'; $bind[':max_price'] = (float)$params['max_price']; }
        if ($params['min_stock'] !== null) { $where[] = 'stock >= :min_stock'; $bind[':min_stock'] = (int)$params['min_stock']; }
        if ($params['max_stock'] !== null) { $where[] = 'stock <= :max_stock'; $bind[':max_stock'] = (int)$params['max_stock']; }
        if ($params['energy'] !== null)    { $where[] = 'energy_rating = :energy'; $bind[':energy'] = (int)$params['energy']; }

        $sortMap = [
            'price_asc'    => 'price ASC',
            'price_desc'   => 'price DESC',
            'name_asc'     => 'name ASC',
            'name_desc'    => 'name DESC',
            'stock_asc'    => 'stock ASC',
            'stock_desc'   => 'stock DESC',
            'created_asc'  => 'created_at ASC',
            'created_desc' => 'created_at DESC',
        ];
        $orderBy = $sortMap[$params['sort']] ?? $sortMap['created_desc'];

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // count
        $stmt = $this->db->prepare("SELECT COUNT(*) cnt FROM appliances $whereSql");
        foreach ($bind as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $total = (int)$stmt->fetchColumn();

        $offset = ($params['page'] - 1) * $params['per_page'];

        // data
        $sql = "SELECT id, sku, name, brand, category, price, stock, warranty_months, energy_rating, created_at, updated_at
                FROM appliances $whereSql ORDER BY $orderBy LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($bind as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $params['per_page'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        Response::ok([
            'items'      => $rows,
            'pagination' => [
                'page'     => $params['page'],
                'per_page' => $params['per_page'],
                'total'    => $total,
                'pages'    => (int)ceil($total / $params['per_page']),
            ],
            'sort'       => $params['sort'],
            'filters'    => array_filter($params, fn($k) => in_array($k, ['category','brand','q','min_price','max_price','min_stock','max_stock','energy']), ARRAY_FILTER_USE_KEY),
        ]);
    }

    // GET /api/appliances/{id}
    public function show(int $id): void {
        $stmt = $this->db->prepare("SELECT * FROM appliances WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) Response::notFound();
        Response::ok($row);
    }

    // POST /api/appliances
    public function create(): void {
        $input = $this->readJsonBody();
        $errors = $this->validatePayload($input, true);
        if ($errors) Response::badRequest(['validation' => $errors]);

        // ตรวจ sku ซ้ำ
        $check = $this->db->prepare("SELECT 1 FROM appliances WHERE sku = :sku");
        $check->execute([':sku' => $input['sku']]);
        if ($check->fetch()) Response::conflict('SKU already exists');

        $sql = "INSERT INTO appliances (sku, name, brand, category, price, stock, warranty_months, energy_rating)
                VALUES (:sku, :name, :brand, :category, :price, :stock, :warranty_months, :energy_rating)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':sku' => $input['sku'],
            ':name' => $input['name'],
            ':brand' => $input['brand'],
            ':category' => $input['category'],
            ':price' => (float)$input['price'],
            ':stock' => (int)$input['stock'],
            ':warranty_months' => (int)$input['warranty_months'],
            ':energy_rating' => $input['energy_rating'] ?? null,
        ]);

        $id = (int)$this->db->lastInsertId();
        $this->show($id); // ส่งรูปแบบเดียวกับ show
    }

    // PUT/PATCH /api/appliances/{id}
    public function update(int $id): void {
        // มีไหม
        $stmt = $this->db->prepare("SELECT * FROM appliances WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();
        if (!$existing) Response::notFound();

        $input = $this->readJsonBody();
        if (!$input) Response::badRequest('Empty body');

        $errors = $this->validatePayload($input, false);
        if ($errors) Response::badRequest(['validation' => $errors]);

        // ถ้ามี sku ใหม่ ต้องไม่ซ้ำกับคนอื่น
        if (isset($input['sku'])) {
            $check = $this->db->prepare("SELECT 1 FROM appliances WHERE sku = :sku AND id <> :id");
            $check->execute([':sku' => $input['sku'], ':id' => $id]);
            if ($check->fetch()) Response::conflict('SKU already exists');
        }

        // build dynamic set
        $fields = [];
        $bind = [':id' => $id];
        $updatable = ['sku','name','brand','category','price','stock','warranty_months','energy_rating'];
        foreach ($updatable as $f) {
            if (array_key_exists($f, $input)) {
                $fields[] = "$f = :$f";
                $bind[":$f"] = $input[$f];
            }
        }
        if (empty($fields)) Response::badRequest('No updatable fields');

        $sql = "UPDATE appliances SET ".implode(', ', $fields)." WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind);

        // ส่งข้อมูลล่าสุด
        $this->show($id);
    }

    // DELETE /api/appliances/{id}
    public function destroy(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM appliances WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) Response::notFound();
        Response::deleted();
    }
}
