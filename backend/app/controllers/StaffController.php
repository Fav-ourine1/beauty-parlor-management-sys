<?php
class StaffController extends Controller
{
    private StaffModel $staff;
    private UserModel $users;

    public function __construct()
    {
        $this->staff = new StaffModel();
        $this->users = new UserModel();
    }

    // GET /api/staff  [admin]
    public function index(array $params): void
    {
        $this->success($this->staff->getAllWithUsers());
    }

    // POST /api/staff  [admin] — creates user account + staff profile together
    public function store(array $params): void
    {
        $data   = $this->getBody();
        $errors = $this->validate($data, ['full_name', 'email', 'phone', 'password', 'job_title']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        if ($this->users->findByEmail(strtolower(trim($data['email'])))) {
            $this->error('Email already registered', 409);
        }

        if ($this->users->findByPhone($data['phone'])) {
            $this->error('Phone number already registered', 409);
        }

        $data['email'] = strtolower(trim($data['email']));
        $userId = $this->users->createUser($data, 'staff');

        $staffId = $this->staff->create([
            'user_id'         => (int) $userId,
            'job_title'       => $data['job_title'],
            'specialisations' => $data['specialisations'] ?? null,
            'hire_date'       => $data['hire_date'] ?? null,
        ]);

        $this->created(['user_id' => $userId, 'staff_id' => $staffId], 'Staff member created');
    }

    // PUT /api/staff/{id}  [admin]
    public function update(array $params): void
    {
        $staffId  = (int) $params['id'];
        $profile  = $this->staff->findById($staffId);

        if (!$profile) {
            $this->notFound('Staff member not found');
        }

        $data = $this->getBody();

        $profileFields = ['job_title', 'specialisations', 'hire_date'];
        $profileUpdate = array_intersect_key($data, array_flip($profileFields));
        if ($profileUpdate) {
            $this->staff->update($staffId, $profileUpdate);
        }

        $userFields = ['full_name', 'phone', 'is_active'];
        $userUpdate = array_intersect_key($data, array_flip($userFields));
        if ($userUpdate) {
            $this->users->update((int) $profile['user_id'], $userUpdate);
        }

        $this->success($this->staff->findById($staffId), 'Staff member updated');
    }

    // GET /api/staff/{id}/shifts  [staff/admin]
    public function shifts(array $params): void
    {
        $staffId = (int) $params['id'];
        $date    = $_GET['date'] ?? null;

        if (!$this->staff->findById($staffId)) {
            $this->notFound('Staff member not found');
        }

        $this->success($this->staff->getShifts($staffId, $date));
    }

    // POST /api/shifts  [admin]
    public function createShift(array $params): void
    {
        $data   = $this->getBody();
        $errors = $this->validate($data, ['staff_id', 'shift_date', 'start_time', 'end_time']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        if (!$this->staff->findById((int) $data['staff_id'])) {
            $this->notFound('Staff member not found');
        }

        $id = $this->staff->createShift([
            'staff_id'   => (int) $data['staff_id'],
            'shift_date' => $data['shift_date'],
            'start_time' => $data['start_time'],
            'end_time'   => $data['end_time'],
            'notes'      => $data['notes'] ?? null,
            'created_by' => $this->currentUserId(),
        ]);

        $this->created(['id' => $id], 'Shift created');
    }

    // POST /api/attendance  [admin]
    public function recordAttendance(array $params): void
    {
        $data   = $this->getBody();
        $errors = $this->validate($data, ['shift_id', 'staff_id', 'status']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        $existing = $this->staff->getShiftWithAttendance((int) $data['shift_id']);
        if (!$existing) {
            $this->notFound('Shift not found');
        }

        $attendanceData = [
            'shift_id'    => (int) $data['shift_id'],
            'staff_id'    => (int) $data['staff_id'],
            'status'      => $data['status'],
            'clock_in_at' => $data['clock_in_at'] ?? null,
            'clock_out_at'=> $data['clock_out_at'] ?? null,
            'notes'       => $data['notes'] ?? null,
            'recorded_by' => $this->currentUserId(),
        ];

        // Update if record exists, otherwise insert
        if (!empty($existing['ar_id'])) {
            $this->staff->updateAttendance((int) $data['shift_id'], $attendanceData);
            $this->success(null, 'Attendance updated');
        }

        $id = $this->staff->recordAttendance($attendanceData);
        $this->created(['id' => $id], 'Attendance recorded');
    }

    // GET /staff/schedule  [staff/admin] — web view
    public function schedule(array $params): void
    {
        $user    = $this->currentUser();
        $profile = $this->staff->findByUserId($user['id']);

        $shifts = $profile
            ? $this->staff->getUpcomingShifts((int) $profile['id'], 60)
            : [];

        include BASE_PATH . '/app/views/staff/schedule.php';
    }

    // GET /staff/dashboard  [staff/admin] — web view
    public function dashboard(array $params): void
    {
        $user    = $this->currentUser();
        $profile = $this->staff->findByUserId($user['id']);

        if (!$profile && $user['role'] !== 'admin') {
            $this->notFound('Staff profile not found');
        }

        $upcomingShifts    = $profile
            ? $this->staff->getUpcomingShifts((int) $profile['id'])
            : [];

        $staffId           = $profile ? (int) $profile['id'] : null;
        $appointments      = new AppointmentModel();
        $todayAppointments = $staffId
            ? $appointments->getByDate(date('Y-m-d'), $staffId)
            : $appointments->getByDate(date('Y-m-d'));

        include BASE_PATH . '/app/views/staff/dashboard.php';
    }
}
