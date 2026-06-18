<?php
class AdminController extends Controller
{
    private AppointmentModel $appointments;
    private StaffModel $staff;
    private ServiceModel $services;
    private ProductModel $products;
    private PaymentModel $payments;

    public function __construct()
    {
        $this->appointments = new AppointmentModel();
        $this->staff        = new StaffModel();
        $this->services     = new ServiceModel();
        $this->products     = new ProductModel();
        $this->payments     = new PaymentModel();
    }

    // GET /admin/appointments
    public function appointments(array $params): void
    {
        $date   = $_GET['date']   ?? null;
        $status = $_GET['status'] ?? null;

        if ($date) {
            $appointments = $this->appointments->getByDate($date);
        } else {
            $appointments = $this->appointments->findAll('appointment_date', 'DESC');
        }

        // Enrich with client/staff names
        $appointments = array_map(
            fn($a) => $this->appointments->getWithDetails((int) $a['id']) ?: $a,
            $appointments
        );

        if ($status) {
            $appointments = array_values(array_filter($appointments, fn($a) => $a['status'] === $status));
        }

        $staffList = $this->staff->getAllWithUsers();

        include BASE_PATH . '/app/views/admin/appointments.php';
    }

    // GET /admin/staff
    public function staff(array $params): void
    {
        $staffList = $this->staff->getAllWithUsers();
        include BASE_PATH . '/app/views/admin/staff.php';
    }

    // GET /admin/services
    public function services(array $params): void
    {
        $services   = $this->services->getAllWithCategories(false);
        $categories = $this->services->getCategories(false);
        include BASE_PATH . '/app/views/admin/services.php';
    }

    // GET /admin/inventory
    public function inventory(array $params): void
    {
        $products   = $this->products->getAllWithCategories(false);
        $categories = $this->products->getCategories();
        $lowStock   = $this->products->getLowStock();
        include BASE_PATH . '/app/views/admin/inventory.php';
    }

    // GET /admin/reports
    public function reports(array $params): void
    {
        $revenue      = $this->payments->getDailyRevenue();
        $todaySummary = $this->appointments->getTodaySummary();
        $lowStock     = $this->products->getLowStock();

        $year         = (int) ($_GET['year']  ?? date('Y'));
        $month        = (int) ($_GET['month'] ?? date('n'));
        $allStaff     = $this->staff->getAllWithUsers();
        $attendance   = [];

        foreach ($allStaff as $member) {
            $records = $this->staff->getMonthlyAttendance((int) $member['id'], $year, $month);
            $attendance[] = [
                'full_name'  => $member['full_name'],
                'job_title'  => $member['job_title'],
                'present'    => count(array_filter($records, fn($r) => ($r['attendance_status'] ?? null) === 'present')),
                'absent'     => count(array_filter($records, fn($r) => ($r['attendance_status'] ?? null) === 'absent')),
                'late'       => count(array_filter($records, fn($r) => ($r['attendance_status'] ?? null) === 'late')),
                'total'      => count($records),
            ];
        }

        include BASE_PATH . '/app/views/admin/reports.php';
    }
}
