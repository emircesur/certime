<?php

namespace App\Models;

use Database;
use PDO;

class Invoice
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int|false
    {
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $amount = (float)($data['amount'] ?? 0);
        $discount = (float)($data['discount_percent'] ?? 0);
        $discountAmt = $amount * ($discount / 100);
        $tax = (float)($data['tax_amount'] ?? 0);
        $total = $amount - $discountAmt + $tax;

        $stmt = $this->db->prepare(
            "INSERT INTO invoices (institution_id, user_id, invoice_number, amount, currency, description, line_items,
             discount_percent, discount_amount, tax_amount, total_amount, status, due_date, notes, created_by)
             VALUES (:iid, :uid, :num, :amt, :cur, :desc, :items, :dp, :da, :tax, :total, :status, :due, :notes, :created)"
        );
        $stmt->bindParam(':iid', $data['institution_id'] ?? null);
        $stmt->bindParam(':uid', $data['user_id'] ?? null);
        $stmt->bindParam(':num', $invoiceNumber);
        $stmt->bindParam(':amt', $amount);
        $stmt->bindValue(':cur', $data['currency'] ?? 'USD');
        $stmt->bindParam(':desc', $data['description'] ?? '');
        $stmt->bindValue(':items', json_encode($data['line_items'] ?? []));
        $stmt->bindParam(':dp', $discount);
        $stmt->bindParam(':da', $discountAmt);
        $stmt->bindParam(':tax', $tax);
        $stmt->bindParam(':total', $total);
        $stmt->bindValue(':status', $data['status'] ?? 'draft');
        $stmt->bindParam(':due', $data['due_date'] ?? null);
        $stmt->bindParam(':notes', $data['notes'] ?? '');
        $stmt->bindValue(':created', currentUserId());

        if ($stmt->execute()) {
            Database::audit('invoice.create', "Invoice {$invoiceNumber} created for amount {$total}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare(
            "SELECT inv.*, i.name as institution_name, u.username, u.email, u.full_name
             FROM invoices inv
             LEFT JOIN institutions i ON inv.institution_id = i.id
             LEFT JOIN users u ON inv.user_id = u.id
             WHERE inv.id = :id"
        );
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT inv.*, i.name as institution_name, u.username
             FROM invoices inv
             LEFT JOIN institutions i ON inv.institution_id = i.id
             LEFT JOIN users u ON inv.user_id = u.id
             ORDER BY inv.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $sql = "UPDATE invoices SET status = :status";
        if ($status === 'paid') $sql .= ", paid_at = CURRENT_TIMESTAMP";
        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();
        if ($result) Database::audit('invoice.status', "Invoice #{$id} marked {$status}");
        return $result;
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['description', 'due_date', 'notes', 'discount_percent', 'tax_amount', 'status', 'payment_method'];
        $sets = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        if (empty($sets)) return false;

        $sql = "UPDATE invoices SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM invoices WHERE status = :s");
        $stmt->bindParam(':s', $status);
        $stmt->execute();
        return (int)$stmt->fetch()['c'];
    }

    public function getTotalRevenue(): float
    {
        $stmt = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE status = 'paid'");
        return (float)$stmt->fetch()['total'];
    }
}
