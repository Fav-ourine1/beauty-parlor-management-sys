<?php
class PaymentController extends Controller
{
    private PaymentModel $payments;
    private AppointmentModel $appointments;

    public function __construct()
    {
        $this->payments     = new PaymentModel();
        $this->appointments = new AppointmentModel();
    }

    // POST /api/payments/mpesa/initiate  [auth]
    public function mpesaInitiate(array $params): void
    {
        $data   = $this->getBody();
        $errors = $this->validate($data, ['appointment_id', 'phone']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        $appointmentId = (int) $data['appointment_id'];
        $appointment   = $this->appointments->findById($appointmentId);

        if (!$appointment) {
            $this->notFound('Appointment not found');
        }

        if ((float) $appointment['total_amount'] <= 0) {
            $this->error('Appointment has no payable amount');
        }

        // Normalise phone to 2547XXXXXXXX format
        $phone = preg_replace('/\D/', '', $data['phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }

        // Create pending payment record
        $paymentId = $this->payments->create([
            'appointment_id' => $appointmentId,
            'amount'         => $appointment['total_amount'],
            'method'         => 'mpesa',
            'status'         => 'pending',
            'recorded_by'    => $this->currentUserId(),
        ]);

        // Get M-Pesa access token
        $token = $this->getMpesaToken();
        if (!$token) {
            $this->serverError('Could not authenticate with M-Pesa');
        }

        $timestamp = date('YmdHis');
        $password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
        $amount    = (int) ceil((float) $appointment['total_amount']);

        $payload = [
            'BusinessShortCode' => MPESA_SHORTCODE,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => $amount,
            'PartyA'            => $phone,
            'PartyB'            => MPESA_SHORTCODE,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => MPESA_CALLBACK_URL,
            'AccountReference'  => 'Mbagathi' . $appointmentId,
            'TransactionDesc'   => 'Beauty parlour appointment #' . $appointmentId,
        ];

        $response = $this->mpesaPost('/mpesa/stkpush/v1/processrequest', $payload, $token);

        if (!$response || ($response['ResponseCode'] ?? '1') !== '0') {
            $this->payments->update((int) $paymentId, ['status' => 'failed']);
            $errorMsg = $response['errorMessage'] ?? 'STK push failed';
            $this->error('M-Pesa request failed: ' . $errorMsg, 502);
        }

        $this->payments->createMpesaTransaction((int) $paymentId, [
            'phone_number'        => $phone,
            'merchant_request_id' => $response['MerchantRequestID'],
            'checkout_request_id' => $response['CheckoutRequestID'],
            'amount'              => $amount,
        ]);

        $this->success([
            'payment_id'          => $paymentId,
            'checkout_request_id' => $response['CheckoutRequestID'],
            'message'             => 'STK push sent. Enter M-Pesa PIN on your phone.',
        ]);
    }

    // POST /api/payments/mpesa/callback  — called by Safaricom Daraja, no auth
    public function mpesaCallback(array $params): void
    {
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true);

        Logger::info('M-Pesa callback: ' . $raw);

        $result = $body['Body']['stkCallback'] ?? null;
        if (!$result) {
            http_response_code(200);
            echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            exit;
        }

        $checkoutId  = $result['CheckoutRequestID'];
        $resultCode  = (int) $result['ResultCode'];
        $resultDesc  = $result['ResultDesc'];

        $transaction = $this->payments->getMpesaByCheckout($checkoutId);
        if (!$transaction) {
            http_response_code(200);
            echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            exit;
        }

        if ($resultCode === 0) {
            $items   = $result['CallbackMetadata']['Item'] ?? [];
            $receipt = '';
            $txDate  = null;

            foreach ($items as $item) {
                match ($item['Name']) {
                    'MpesaReceiptNumber' => ($receipt = $item['Value']),
                    'TransactionDate'    => ($txDate  = date('Y-m-d H:i:s', strtotime((string) $item['Value']))),
                    default              => null,
                };
            }

            $this->payments->updateMpesaTransaction($checkoutId, [
                'mpesa_receipt'      => $receipt,
                'result_code'        => 0,
                'result_description' => $resultDesc,
                'transaction_date'   => $txDate,
            ]);

            $this->payments->completePayment((int) $transaction['payment_id'], 'mpesa');
            $this->appointments->updateStatus(
                (int) $this->payments->getWithAppointment((int) $transaction['payment_id'])['appointment_id'],
                'confirmed'
            );
        } else {
            $this->payments->updateMpesaTransaction($checkoutId, [
                'result_code'        => $resultCode,
                'result_description' => $resultDesc,
            ]);
            $this->payments->update((int) $transaction['payment_id'], ['status' => 'failed']);
        }

        http_response_code(200);
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        exit;
    }

    // POST /api/payments/cash  [staff/admin]
    public function recordCash(array $params): void
    {
        $data   = $this->getBody();
        $errors = $this->validate($data, ['appointment_id', 'amount']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        $appointmentId = (int) $data['appointment_id'];
        $appointment   = $this->appointments->findById($appointmentId);

        if (!$appointment) {
            $this->notFound('Appointment not found');
        }

        $paymentId = $this->payments->create([
            'appointment_id' => $appointmentId,
            'amount'         => (float) $data['amount'],
            'method'         => 'cash',
            'status'         => 'completed',
            'paid_at'        => date('Y-m-d H:i:s'),
            'notes'          => $data['notes'] ?? null,
            'recorded_by'    => $this->currentUserId(),
        ]);

        $this->appointments->updateStatus($appointmentId, 'completed');

        $this->created(['payment_id' => $paymentId], 'Cash payment recorded');
    }

    // ── M-Pesa helpers ────────────────────────────────────────

    private function getMpesaToken(): string|false
    {
        $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

        $ch = curl_init(MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $credentials],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? false;
    }

    private function mpesaPost(string $endpoint, array $payload, string $token): array|false
    {
        $ch = curl_init(MPESA_BASE_URL . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? false;
    }
}
