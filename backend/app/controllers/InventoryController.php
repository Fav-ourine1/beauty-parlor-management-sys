<?php
class InventoryController extends Controller
{
    private ProductModel $products;

    public function __construct()
    {
        $this->products = new ProductModel();
    }

    // GET /api/products  [staff/admin]
    public function index(array $params): void
    {
        $lowOnly = isset($_GET['low_stock']);

        if ($lowOnly) {
            $this->success($this->products->getLowStock());
        }

        $activeOnly = !isset($_GET['all']);
        $this->success($this->products->getAllWithCategories(!$activeOnly));
    }

    // GET /api/products/{id}  [staff/admin]
    public function show(array $params): void
    {
        $product = $this->products->findById((int) $params['id']);
        if (!$product) {
            $this->notFound('Product not found');
        }
        $this->success($product);
    }

    // POST /api/products  [admin]
    public function store(array $params): void
    {
        $data   = $this->getBody();
        $errors = $this->validate($data, ['category_id', 'name', 'unit']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        if (!empty($data['sku']) && $this->products->findBySku($data['sku'])) {
            $this->error('SKU already exists', 409);
        }

        $openingStock = (int) ($data['current_stock'] ?? 0);

        $id = $this->products->create([
            'category_id'        => (int) $data['category_id'],
            'name'               => $data['name'],
            'brand'              => $data['brand'] ?? null,
            'sku'                => $data['sku'] ?? null,
            'unit'               => $data['unit'],
            'current_stock'      => 0,
            'low_stock_threshold'=> (int) ($data['low_stock_threshold'] ?? 5),
            'reorder_quantity'   => (int) ($data['reorder_quantity'] ?? 10),
            'unit_cost'          => (float) ($data['unit_cost'] ?? 0),
            'is_active'          => 1,
        ]);

        if ($openingStock > 0) {
            $this->products->adjustStock((int) $id, $openingStock, [
                'movement_type' => 'purchase',
                'notes'         => 'Opening stock',
                'recorded_by'   => $this->currentUserId(),
            ]);
        }

        $this->created(['id' => $id], 'Product added');
    }

    // PUT /api/products/{id}  [admin]
    public function update(array $params): void
    {
        $id      = (int) $params['id'];
        $product = $this->products->findById($id);

        if (!$product) {
            $this->notFound('Product not found');
        }

        $data    = $this->getBody();
        $allowed = ['category_id', 'name', 'brand', 'sku', 'unit', 'low_stock_threshold', 'reorder_quantity', 'unit_cost', 'is_active'];
        $update  = array_intersect_key($data, array_flip($allowed));

        if (empty($update)) {
            $this->error('No valid fields to update');
        }

        if (isset($update['sku'])) {
            $existing = $this->products->findBySku($update['sku']);
            if ($existing && (int) $existing['id'] !== $id) {
                $this->error('SKU already in use', 409);
            }
        }

        $this->products->update($id, $update);
        $this->success($this->products->findById($id), 'Product updated');
    }

    // POST /api/stock-movements  [staff/admin]
    public function recordMovement(array $params): void
    {
        $data   = $this->getBody();
        $errors = $this->validate($data, ['product_id', 'movement_type', 'quantity_change']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        $product = $this->products->findById((int) $data['product_id']);
        if (!$product) {
            $this->notFound('Product not found');
        }

        $change = (int) $data['quantity_change'];
        $newStock = (int) $product['current_stock'] + $change;

        if ($newStock < 0) {
            $this->error("Insufficient stock. Current stock: {$product['current_stock']}", 422);
        }

        $this->products->adjustStock((int) $data['product_id'], $change, [
            'movement_type' => $data['movement_type'],
            'notes'         => $data['notes'] ?? null,
            'recorded_by'   => $this->currentUserId(),
            'reference_id'  => $data['reference_id'] ?? null,
        ]);

        $this->success(['new_stock' => $newStock], 'Stock movement recorded');
    }

    // GET /api/products/{id}/movements  [staff/admin]
    public function movements(array $params): void
    {
        $id = (int) $params['id'];
        if (!$this->products->findById($id)) {
            $this->notFound('Product not found');
        }

        $limit = min((int) ($_GET['limit'] ?? 50), 200);
        $this->success($this->products->getMovements($id, $limit));
    }
}
