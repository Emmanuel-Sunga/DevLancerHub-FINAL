<?php

namespace App\Services;

use App\Models\Payment;

class PaymentService
{
    private JsonDatabase $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $this->db = new JsonDatabase($config['payments_file']);
    }

    public function createPayment(array $data): Payment
    {
        $paymentData = [
            'job_id' => (int)$data['job_id'],
            'employer_id' => (int)$data['employer_id'],
            'employee_id' => (int)$data['employee_id'],
            'amount' => (float)$data['amount'],
            'status' => 'pending',
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'description' => trim($data['description'] ?? ''),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->create($paymentData);
        $paymentData['id'] = $id;

        return new Payment($paymentData);
    }

    public function getPaymentById(int $id): ?Payment
    {
        $data = $this->db->findById($id);
        return $data ? new Payment($data) : null;
    }

    public function getPaymentsByUser(int $userId): array
    {
        return array_map(fn($data) => new Payment($data), 
            $this->db->findWhere(fn($p) => $p['employer_id'] === $userId || $p['employee_id'] === $userId)
        );
    }

    public function getPaymentsByJob(int $jobId): array
    {
        return array_map(fn($data) => new Payment($data), 
            $this->db->findWhere(fn($p) => $p['job_id'] === $jobId)
        );
    }

    public function completePayment(int $paymentId): bool
    {
        $payment = $this->db->findById($paymentId);
        if (!$payment) {
            return false;
        }

        return $this->db->update($paymentId, [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function cancelPayment(int $paymentId): bool
    {
        return $this->db->update($paymentId, [
            'status' => 'cancelled'
        ]);
    }

    public function getTotalEarnings(int $employeeId): float
    {
        $payments = $this->db->findWhere(fn($p) => $p['employee_id'] === $employeeId && $p['status'] === 'completed');
        return array_sum(array_column($payments, 'amount'));
    }

    public function getTotalSpent(int $employerId): float
    {
        $payments = $this->db->findWhere(fn($p) => $p['employer_id'] === $employerId && $p['status'] === 'completed');
        return array_sum(array_column($payments, 'amount'));
    }
}

