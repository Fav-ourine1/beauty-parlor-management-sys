<?php
class ReportController extends Controller
{
    private PaymentModel $payments;
    private AppointmentModel $appointments;
    private ProductModel $products;
    private StaffModel $staff;

    public function __construct()
    {
        $this->payments     = new PaymentModel();
        $this->appointments = new AppointmentModel();
        $this->products     = new ProductModel();
        $this->staff        = new StaffModel();
    }

    // GET /api/reports/revenue  [admin]
    public function revenue(array $params): void
    {
        $date = $_GET['date'] ?? null;
        $this->success($this->payments->getDailyRevenue($date));
    }

    // GET /api/reports/appointments  [admin]
    public function appointments(array $params): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $this->success([
            'summary'      => $this->appointments->getTodaySummary(),
            'appointments' => $this->appointments->getByDate($date),
        ]);
    }

    // GET /api/reports/low-stock  [staff/admin]
    public function lowStock(array $params): void
    {
        $this->success($this->products->getLowStock());
    }

    // GET /api/reports/attendance  [admin]
    public function attendance(array $params): void
    {
        $year  = (int) ($_GET['year']  ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        $allStaff = $this->staff->getAllWithUsers();
        $report   = [];

        foreach ($allStaff as $member) {
            $records = $this->staff->getMonthlyAttendance((int) $member['id'], $year, $month);
            $report[] = [
                'staff_id'   => $member['id'],
                'full_name'  => $member['full_name'],
                'job_title'  => $member['job_title'],
                'present'    => count(array_filter($records, fn($r) => $r['status'] === 'present')),
                'absent'     => count(array_filter($records, fn($r) => $r['status'] === 'absent')),
                'late'       => count(array_filter($records, fn($r) => $r['status'] === 'late')),
                'half_day'   => count(array_filter($records, fn($r) => $r['status'] === 'half_day')),
                'total'      => count($records),
            ];
        }

        $this->success(['year' => $year, 'month' => $month, 'staff' => $report]);
    }

    // GET /admin/dashboard — web view with combined stats  [admin]
    public function adminDashboard(array $params): void
    {
        $todaySummary = $this->appointments->getTodaySummary();
        $todayRevenue = $this->payments->getDailyRevenue();
        $lowStock     = $this->products->getLowStock();
        $appointments = $this->appointments->getByDate(date('Y-m-d'));

        // Enrich appointments with client/staff names via getWithDetails
        $appointments = array_map(
            fn($a) => $this->appointments->getWithDetails((int) $a['id']) ?: $a,
            $appointments
        );

        include BASE_PATH . '/app/views/admin/dashboard.php';
    }
}
