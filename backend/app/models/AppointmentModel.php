<?php
class AppointmentModel extends Model
{
    protected string $table = 'appointments';

    public function getWithDetails(int $id): array|false
    {
        return $this->db->fetchOne(
            'SELECT a.*,
                    uc.full_name  AS client_name,
                    uc.email      AS client_email,
                    uc.phone      AS client_phone,
                    us.full_name  AS staff_name,
                    sp.job_title  AS staff_job_title
             FROM appointments a
             JOIN clients c        ON a.client_id  = c.id
             JOIN users uc         ON c.user_id     = uc.id
             LEFT JOIN staff_profiles sp ON a.staff_id = sp.id
             LEFT JOIN users us    ON sp.user_id    = us.id
             WHERE a.id = ? LIMIT 1',
            [$id]
        );
    }

    public function getByClient(int $clientId, ?string $status = null): array
    {
        if ($status !== null) {
            return $this->db->fetchAll(
                'SELECT * FROM appointments WHERE client_id = ? AND status = ? ORDER BY appointment_date DESC, start_time DESC',
                [$clientId, $status]
            );
        }

        return $this->db->fetchAll(
            'SELECT * FROM appointments WHERE client_id = ? ORDER BY appointment_date DESC, start_time DESC',
            [$clientId]
        );
    }

    public function getByDate(string $date, ?int $staffId = null): array
    {
        if ($staffId !== null) {
            return $this->db->fetchAll(
                'SELECT * FROM appointments WHERE appointment_date = ? AND staff_id = ? ORDER BY start_time',
                [$date, $staffId]
            );
        }

        return $this->db->fetchAll(
            'SELECT * FROM appointments WHERE appointment_date = ? ORDER BY start_time',
            [$date]
        );
    }

    public function getByStaff(int $staffId, ?string $date = null): array
    {
        if ($date !== null) {
            return $this->db->fetchAll(
                'SELECT * FROM appointments WHERE staff_id = ? AND appointment_date = ? ORDER BY start_time',
                [$staffId, $date]
            );
        }

        return $this->db->fetchAll(
            'SELECT * FROM appointments WHERE staff_id = ? ORDER BY appointment_date DESC, start_time DESC',
            [$staffId]
        );
    }

    public function getServices(int $appointmentId): array
    {
        return $this->db->fetchAll(
            'SELECT aps.*, s.name, s.description, s.duration_mins
             FROM appointment_services aps
             JOIN services s ON aps.service_id = s.id
             WHERE aps.appointment_id = ?',
            [$appointmentId]
        );
    }

    public function createWithServices(array $appointment, array $serviceIds, array $prices): string
    {
        $this->db->beginTransaction();

        try {
            $columns      = implode(', ', array_keys($appointment));
            $placeholders = implode(', ', array_fill(0, count($appointment), '?'));

            $appointmentId = $this->db->insert(
                "INSERT INTO appointments ({$columns}) VALUES ({$placeholders})",
                array_values($appointment)
            );

            foreach ($serviceIds as $index => $serviceId) {
                $this->db->insert(
                    'INSERT INTO appointment_services (appointment_id, service_id, price_at_booking) VALUES (?, ?, ?)',
                    [(int) $appointmentId, $serviceId, $prices[$index]]
                );
            }

            $this->db->commit();

            return $appointmentId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status): int
    {
        return $this->db->execute(
            'UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ?',
            [$status, $id]
        );
    }

    public function cancel(int $id, string $reason): int
    {
        return $this->db->execute(
            "UPDATE appointments SET status = 'cancelled', cancelled_at = NOW(), cancel_reason = ? WHERE id = ?",
            [$reason, $id]
        );
    }

    public function getTodaySummary(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS count
             FROM appointments
             WHERE appointment_date = CURDATE()
             GROUP BY status"
        );

        $summary = [
            'pending'     => 0,
            'confirmed'   => 0,
            'in_progress' => 0,
            'completed'   => 0,
            'cancelled'   => 0,
        ];

        foreach ($rows as $row) {
            if (array_key_exists($row['status'], $summary)) {
                $summary[$row['status']] = (int) $row['count'];
            }
        }

        return $summary;
    }
}
