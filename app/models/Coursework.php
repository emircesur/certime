<?php

namespace App\Models;

use Database;
use PDO;

class Coursework
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Column mapping: form/view names → actual DB column names
     */
    private static array $colMap = [
        'course_name' => 'title',
        'credits'     => 'credit_hours',
        'term'        => 'semester',
        'institution' => 'instructor',
    ];

    private static string $ALIASES =
        "*, title AS course_name, credit_hours AS credits, semester AS term, instructor AS institution";

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT " . self::$ALIASES . " FROM coursework WHERE user_id = :uid ORDER BY semester DESC, course_code ASC"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT " . self::$ALIASES . " FROM coursework WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create(int $userId, string $courseCode, string $courseName, float $credits, string $grade = '', string $term = '', string $institution = '', int $credentialId = 0): int|false
    {
        $stmt = $this->db->prepare(
            "INSERT INTO coursework (user_id, course_code, title, credit_hours, grade, semester, instructor, credential_id)
             VALUES (:uid, :code, :name, :credits, :grade, :term, :inst, :cid)"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':code', $courseCode);
        $stmt->bindParam(':name', $courseName);
        $stmt->bindParam(':credits', $credits);
        $stmt->bindParam(':grade', $grade);
        $stmt->bindParam(':term', $term);
        $stmt->bindParam(':inst', $institution);
        $stmt->bindParam(':cid', $credentialId);

        if ($stmt->execute()) {
            Database::audit('coursework.create', "User {$userId} added coursework: {$courseCode}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function update(int $id, array $data): bool
    {
        // Map view field names to actual DB column names
        $mapped = [];
        foreach ($data as $key => $value) {
            $dbCol = self::$colMap[$key] ?? $key;
            $mapped[$dbCol] = $value;
        }

        $allowed = ['course_code', 'title', 'credit_hours', 'grade', 'semester', 'instructor', 'status', 'credential_id'];
        $sets = [];
        $params = [':id' => $id];

        foreach ($mapped as $key => $value) {
            if (in_array($key, $allowed)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        if (empty($sets)) return false;

        $sql = "UPDATE coursework SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM coursework WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getGPA(int $userId): float
    {
        $courses = $this->findByUser($userId);
        if (empty($courses)) return 0.0;

        $gradePoints = [
            'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
            'F' => 0.0,
        ];

        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($courses as $c) {
            $g = strtoupper(trim($c['grade'] ?? ''));
            if (isset($gradePoints[$g]) && (float)($c['credits'] ?? $c['credit_hours'] ?? 0) > 0) {
                $cr = (float)($c['credits'] ?? $c['credit_hours'] ?? 0);
                $totalPoints += $gradePoints[$g] * $cr;
                $totalCredits += $cr;
            }
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }

    public function getTotalCredits(int $userId): float
    {
        $stmt = $this->db->prepare("SELECT SUM(credit_hours) as total FROM coursework WHERE user_id = :uid AND status = 'completed'");
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return (float)($result['total'] ?? 0);
    }

    public function getTerms(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT semester AS term FROM coursework WHERE user_id = :uid AND semester != '' ORDER BY semester DESC");
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return array_column($stmt->fetchAll(), 'term');
    }
}
