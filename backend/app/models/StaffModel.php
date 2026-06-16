<?php
class StaffModel extends Model
{
    protected string $table = 'staff_profiles';

    public function findByUserId(int $userId): array|false
    {
        return $this->db->fetchOne(
            'SELECT * FROM staff_profiles WHERE user_id = ? LIMIT 1',
            [$userId]
        );
    }

    public function getAllWithUsers(): array
    {
        return $this->db->fetchAll(
            'SELECT sp.*, u.full_name, u.email, u.phone, u.is_active
             FROM staff_profiles sp
             JOIN users u ON sp.user_id = u.id
             ORDER BY u.full_name ASC'
        );
    }

    public function getShifts(int $staffId, ?string $date = null): array
    {
        if ($date !== null) {
            return $this->db->fetchAll(
                'SELECT * FROM shifts WHERE staff_id = ? AND shift_date = ? ORDER BY shift_date, start_time',
                [$staffId, $date]
            );
        }

        return $this->db->fetchAll(
            'SELECT * FROM shifts WHERE staff_id = ? ORDER BY shift_date, start_time',
            [$staffId]
        );
    }

    public function createShift(array $data): string
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        return $this->db->insert(
            "INSERT INTO shifts ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    public function getShiftWithAttendance(int $shiftId): array|false
    {
        return $this->db->fetchOne(
            'SELECT s.*, ar.clock_in_at, ar.clock_out_at, ar.status AS attendance_status,
                    ar.notes AS attendance_notes, ar.recorded_by, ar.id AS attendance_id
             FROM shifts s
             LEFT JOIN attendance_records ar ON ar.shift_id = s.id
             WHERE s.id = ? LIMIT 1',
            [$shiftId]
        );
    }

    public function recordAttendance(array $data): string
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        return $this->db->insert(
            "INSERT INTO attendance_records ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    public function updateAttendance(int $shiftId, array $data): int
    {
        $clauses = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));

        return $this->db->execute(
            "UPDATE attendance_records SET {$clauses} WHERE shift_id = ?",
            [...array_values($data), $shiftId]
        );
    }

    public function getMonthlyAttendance(int $staffId, int $year, int $month): array
    {
        return $this->db->fetchAll(
            'SELECT s.*, ar.clock_in_at, ar.clock_out_at, ar.status AS attendance_status,
                    ar.notes AS attendance_notes, ar.recorded_by, ar.id AS attendance_id
             FROM shifts s
             LEFT JOIN attendance_records ar ON ar.shift_id = s.id
             WHERE s.staff_id = ?
               AND YEAR(s.shift_date) = ?
               AND MONTH(s.shift_date) = ?
             ORDER BY s.shift_date, s.start_time',
            [$staffId, $year, $month]
        );
    }

    public function getUpcomingShifts(int $staffId, int $limit = 7): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM shifts WHERE staff_id = ? AND shift_date >= CURDATE() ORDER BY shift_date, start_time LIMIT ?',
            [$staffId, $limit]
        );
    }
}
