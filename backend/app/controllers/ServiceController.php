<?php
class ServiceController extends Controller
{
    private ServiceModel $services;

    public function __construct()
    {
        $this->services = new ServiceModel();
    }

    // GET /api/services
    public function index(array $params): void
    {
        $activeOnly = !(isset($_GET['all']) && $this->hasRole('admin'));
        $this->success($this->services->getAllWithCategories($activeOnly));
    }

    // GET /api/services/{id}
    public function show(array $params): void
    {
        $service = $this->services->findById((int) $params['id']);
        if (!$service) {
            $this->notFound('Service not found');
        }
        $this->success($service);
    }

    // POST /api/services  [admin]
    public function store(array $params): void
    {
        $data   = $this->getBody();
        $errors = $this->validate($data, ['category_id', 'name', 'price', 'duration_mins']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        if (!$this->services->getCategoryById((int) $data['category_id'])) {
            $this->error('Category not found', 404);
        }

        $id = $this->services->create([
            'category_id'   => (int) $data['category_id'],
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'price'         => (float) $data['price'],
            'duration_mins' => (int) $data['duration_mins'],
            'is_active'     => 1,
        ]);

        $this->created(['id' => $id], 'Service created');
    }

    // PUT /api/services/{id}  [admin]
    public function update(array $params): void
    {
        $id      = (int) $params['id'];
        $service = $this->services->findById($id);
        if (!$service) {
            $this->notFound('Service not found');
        }

        $data    = $this->getBody();
        $allowed = ['category_id', 'name', 'description', 'price', 'duration_mins', 'is_active'];
        $update  = array_intersect_key($data, array_flip($allowed));

        if (empty($update)) {
            $this->error('No valid fields to update');
        }

        $this->services->update($id, $update);
        $this->success($this->services->findById($id), 'Service updated');
    }

    // DELETE /api/services/{id}  [admin]
    public function destroy(array $params): void
    {
        $id      = (int) $params['id'];
        $service = $this->services->findById($id);
        if (!$service) {
            $this->notFound('Service not found');
        }

        // Soft-delete: mark inactive rather than hard-delete to preserve booking history
        $this->services->update($id, ['is_active' => 0]);
        $this->success(null, 'Service deactivated');
    }

    // GET /api/service-categories
    public function categories(array $params): void
    {
        $this->success($this->services->getCategories());
    }

    // POST /api/service-categories  [admin]
    public function storeCategory(array $params): void
    {
        $data   = $this->getBody();
        $errors = $this->validate($data, ['name']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        $id = $this->services->createCategory([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => 1,
        ]);

        $this->created(['id' => $id], 'Category created');
    }
}
