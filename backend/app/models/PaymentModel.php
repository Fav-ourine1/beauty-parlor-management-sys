<?php
class PaymentModel extends Model
{
    protected string $table = 'payments';

    public function getByAppointment(int $appointmentId): array|false
    {
        return $this->db->fetchOne(
            'SELECT * FROM payments WHERE appointment_id = ? LIMIT 1',
            [$appointmentId]
        );
    }

    public function createMpesaTransaction(int $paymentId, array $mpesaData): string
    {
        $record = array_merge(['payment_id' => $paymentId], $mpesaData);

        $columns      = implode(', ', array_keys($record));
        $placeholders = implode(', ', array_fill(0, count($record), '?'));

        return $this->db->insert(
            "INSERT INTO mpesa_transactions ({$columns}) VALUES ({$placeholders})",
            array_values($record)
        );
    }

    public function getMpesaByCheckout(string $checkoutRequestId): array|false
    {
        return $this->db->fetchOne(
            'SELECT * FROM mpesa_transactions WHERE checkout_request_id = ? LIMIT 1',
            [$checkoutRequestId]
        );
    }

    public function updateMpesaTransaction(string $checkoutRequestId, array $data): int
    {
        $clauses = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));

        return $this->db->execute(
            "UPDATE mpesa_transactions SET {$clauses} WHERE checkout_request_id = ?",
            [...array_values($data), $checkoutRequestId]
        );
    }

    public function completePayment(int $paymentId, string $method): int
    {
        return $this->db->execute(
            "UPDATE payments SET status = 'completed', method = ?, paid_at = NOW() WHERE id = ?",
            [$method, $paymentId]
        );
    }

    public function getWithAppointment(int $paymentId): array|false
    {
        return $this->db->fetchOne(
            'SELECT p.*, a.appointment_date, a.start_time, a.end_time,
                    a.status AS appointment_status, a.total_amount, a.client_id, a.staff_id
             FROM payments p
             JOIN appointments a ON p.appointment_id = a.id
             WHERE p.id = ? LIMIT 1',
            [$paymentId]
        );
    }

    public function getDailyRevenue(?string $date = null): array
    {
        if ($date !== null) {
            return $this->db->fetchAll(
                'SELECT * FROM v_daily_revenue WHERE revenue_date = ?',
                [$date]
            );
        }

        return $this->db->fetchAll('SELECT * FROM v_daily_revenue ORDER BY revenue_date DESC');
    }
}
