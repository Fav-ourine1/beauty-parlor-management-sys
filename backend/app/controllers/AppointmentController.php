<?php
class AppointmentController extends Controller
{
    private AppointmentModel $appointments;
    private ServiceModel $services;
    private ClientModel $clients;

    public function __construct()
    {
        $this->appointments = new AppointmentModel();
        $this->services     = new ServiceModel();
        $this->clients      = new ClientModel();
    }

    // GET /api/appointments
    public function index(array $params): void
    {
        $user = $this->currentUser();

        if ($user['role'] === 'client') {
            $client = $this->clients->findByUserId($user['id']);
            if (!$client) {
                $this->success([]);
            }
            $status = $_GET['status'] ?? null;
            $this->success($this->appointments->getByClient((int) $client['id'], $status));
        }

        // staff / admin: filter by date or staff
        $date    = $_GET['date']     ?? null;
        $staffId = $_GET['staff_id'] ?? null;

        if ($date) {
            $this->success($this->appointments->getByDate($date, $staffId ? (int) $staffId : null));
        }

        $this->success($this->appointments->findAll('appointment_date', 'DESC'));
    }

    // GET /api/appointments/{id}
    public function show(array $params): void
    {
        $id          = (int) $params['id'];
        $appointment = $this->appointments->getWithDetails($id);

        if (!$appointment) {
            $this->notFound('Appointment not found');
        }

        $this->assertCanView($appointment);

        $appointment['services'] = $this->appointments->getServices($id);
        $this->success($appointment);
    }

    // POST /api/appointments
    public function store(array $params): void
    {
        $user   = $this->currentUser();
        $data   = $this->getBody();
        $errors = $this->validate($data, ['appointment_date', 'start_time', 'end_time', 'service_ids']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        if (empty($data['service_ids']) || !is_array($data['service_ids'])) {
            $this->error('At least one service must be selected', 422);
        }

        // Resolve client_id
        if ($user['role'] === 'client') {
            $client = $this->clients->findByUserId($user['id']);
            if (!$client) {
                $this->error('Client profile not found', 404);
            }
            $clientId = (int) $client['id'];
        } else {
            if (empty($data['client_id'])) {
                $this->error('client_id is required', 422);
            }
            $clientId = (int) $data['client_id'];
        }

        // Validate and price services
        $serviceIds = array_map('intval', $data['service_ids']);
        $svcRows    = $this->services->getServicesById($serviceIds);

        if (count($svcRows) !== count($serviceIds)) {
            $this->error('One or more services not found', 404);
        }

        $prices = array_map(fn($svc) => (float) $svc['price'], array_values($svcRows));
        $total  = array_sum($prices);

        $appointment = [
            'client_id'        => $clientId,
            'staff_id'         => isset($data['staff_id']) ? (int) $data['staff_id'] : null,
            'appointment_date' => $data['appointment_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'status'           => 'pending',
            'notes'            => $data['notes'] ?? null,
            'total_amount'     => $total,
        ];

        $id = $this->appointments->createWithServices($appointment, $serviceIds, $prices);
        $this->created(['id' => $id, 'total_amount' => $total], 'Appointment booked');
    }

    // PUT /api/appointments/{id}  [staff/admin]
    public function update(array $params): void
    {
        $id          = (int) $params['id'];
        $appointment = $this->appointments->findById($id);

        if (!$appointment) {
            $this->notFound('Appointment not found');
        }

        $data    = $this->getBody();
        $allowed = ['staff_id', 'appointment_date', 'start_time', 'end_time', 'status', 'notes'];
        $update  = array_intersect_key($data, array_flip($allowed));

        if (empty($update)) {
            $this->error('No valid fields to update');
        }

        $this->appointments->update($id, $update);
        $this->success($this->appointments->getWithDetails($id), 'Appointment updated');
    }

    // DELETE /api/appointments/{id}
    public function cancel(array $params): void
    {
        $id          = (int) $params['id'];
        $appointment = $this->appointments->findById($id);

        if (!$appointment) {
            $this->notFound('Appointment not found');
        }

        $this->assertCanView($appointment);

        if (in_array($appointment['status'], ['completed', 'cancelled'], true)) {
            $this->error("Cannot cancel a {$appointment['status']} appointment");
        }

        $data   = $this->getBody();
        $reason = $data['reason'] ?? 'Cancelled by user';

        $this->appointments->cancel($id, $reason);
        $this->success(null, 'Appointment cancelled');
    }

    // GET /api/appointments/today  [staff/admin]
    public function today(array $params): void
    {
        $appointments = $this->appointments->getByDate(date('Y-m-d'));
        $summary      = $this->appointments->getTodaySummary();

        $this->success(['summary' => $summary, 'appointments' => $appointments]);
    }

    private function assertCanView(array $appointment): void
    {
        $user = $this->currentUser();
        if ($user['role'] === 'admin' || $user['role'] === 'staff') {
            return;
        }

        $client = $this->clients->findByUserId($user['id']);
        if (!$client || (int) $client['id'] !== (int) $appointment['client_id']) {
            $this->forbidden();
        }
    }
}
