<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Reclamation {
    private $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Create a new reclamation (main table)
     */
    public function create(int $user_id, string $sujet, string $description, ?string $full_name = null, ?string $mobile_phone = null, string $priority = 'normal'): bool {
        $sql = "INSERT INTO reclamation (user_id, sujet, description, full_name, mobile_phone, priority) 
                VALUES (:user_id, :sujet, :description, :full_name, :mobile_phone, :priority)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id'=>$user_id,
            ':sujet'=>$sujet,
            ':description'=>$description,
            ':full_name'=>$full_name,
            ':mobile_phone'=>$mobile_phone,
            ':priority'=>$priority
        ]);
    }

    /**
     * Update reclamation fields (main table)
     */
    public function update(int $id, array $fields): bool {
        $set = []; $params = [':id' => $id];
        foreach ($fields as $col => $val) {
            $set[] = "`$col` = :$col";
            $params[":$col"] = $val;
        }
        $sql = "UPDATE reclamation SET " . implode(',', $set) . " WHERE id_reclamation = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM reclamation WHERE id_reclamation = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id'=>$id]);
    }

    /**
     * Find a reclamation and attach the latest response (if any) and responder name.
     * Returns a single associative array or false.
     */
    public function find(int $id) {
        $sql = "
            SELECT 
                r.*,
                u.nom AS user_name,
                (SELECT rr.response_text FROM reclamation_response rr WHERE rr.reclamation_id = r.id_reclamation ORDER BY rr.id_response DESC LIMIT 1) AS response_text,
                (SELECT rr.response_date FROM reclamation_response rr WHERE rr.reclamation_id = r.id_reclamation ORDER BY rr.id_response DESC LIMIT 1) AS response_date,
                (SELECT rr.responder_id FROM reclamation_response rr WHERE rr.reclamation_id = r.id_reclamation ORDER BY rr.id_response DESC LIMIT 1) AS responder_id,
                (SELECT u2.nom FROM user u2 WHERE u2.id_user = (
                    SELECT rr2.responder_id FROM reclamation_response rr2 WHERE rr2.reclamation_id = r.id_reclamation ORDER BY rr2.id_response DESC LIMIT 1
                ) LIMIT 1) AS responder_name
            FROM reclamation r
            LEFT JOIN user u ON r.user_id = u.id_user
            WHERE r.id_reclamation = :id
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Return all reclamations with latest response info (for admin listing).
     * Uses positional parameters to avoid PDO named-parameter mismatch errors.
     */
    public function all(int $limit = 100, int $offset = 0, string $search = '') {
        $baseSql = "
            SELECT
                r.*,
                u.nom AS user_name,
                (SELECT rr.response_text FROM reclamation_response rr WHERE rr.reclamation_id = r.id_reclamation ORDER BY rr.id_response DESC LIMIT 1) AS response_text,
                (SELECT rr.response_date FROM reclamation_response rr WHERE rr.reclamation_id = r.id_reclamation ORDER BY rr.id_response DESC LIMIT 1) AS response_date,
                (SELECT rr.responder_id FROM reclamation_response rr WHERE rr.reclamation_id = r.id_reclamation ORDER BY rr.id_response DESC LIMIT 1) AS responder_id,
                (SELECT u2.nom FROM user u2 WHERE u2.id_user = (
                    SELECT rr2.responder_id FROM reclamation_response rr2 WHERE rr2.reclamation_id = r.id_reclamation ORDER BY rr2.id_response DESC LIMIT 1
                ) LIMIT 1) AS responder_name
            FROM reclamation r
            JOIN user u ON r.user_id = u.id_user
        ";

        $params = [];
        if (trim($search) !== '') {
            $baseSql .= " WHERE (r.sujet LIKE ? OR r.description LIKE ? OR u.nom LIKE ? OR r.priority LIKE ? OR r.statut LIKE ?)";
            $sval = '%' . $search . '%';
            for ($i = 0; $i < 5; $i++) $params[] = $sval;
        }

        $baseSql .= " ORDER BY r.date_creation DESC LIMIT ? OFFSET ?";

        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->pdo->prepare($baseSql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count total (optionally filtered by search) - useful for pagination / UI.
     */
    public function countAll(string $search = ''): int {
        $sql = "SELECT COUNT(*) as c FROM reclamation r JOIN user u ON r.user_id = u.id_user";
        $params = [];
        if (trim($search) !== '') {
            $sql .= " WHERE (r.sujet LIKE ? OR r.description LIKE ? OR u.nom LIKE ? OR r.priority LIKE ? OR r.statut LIKE ?)";
            $sval = '%' . $search . '%';
            for ($i = 0; $i < 5; $i++) $params[] = $sval;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int)($row['c'] ?? 0);
    }

    /**
     * Return all reclamations for a specific user (front listing)
     */
    public function allByUser(int $user_id, int $limit = 100, int $offset = 0) {
        $sql = "
            SELECT
                r.*,
                (SELECT rr.response_text FROM reclamation_response rr WHERE rr.reclamation_id = r.id_reclamation ORDER BY rr.id_response DESC LIMIT 1) AS response_text,
                (SELECT rr.response_date FROM reclamation_response rr WHERE rr.reclamation_id = r.id_reclamation ORDER BY rr.id_response DESC LIMIT 1) AS response_date,
                (SELECT rr.responder_id FROM reclamation_response rr WHERE rr.reclamation_id = r.id_reclamation ORDER BY rr.id_response DESC LIMIT 1) AS responder_id,
                (SELECT u2.nom FROM user u2 WHERE u2.id_user = (
                    SELECT rr2.responder_id FROM reclamation_response rr2 WHERE rr2.reclamation_id = r.id_reclamation ORDER BY rr2.id_response DESC LIMIT 1
                ) LIMIT 1) AS responder_name
            FROM reclamation r
            WHERE r.user_id = :user_id
            ORDER BY r.date_creation DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Admin respond: insert a new row into reclamation_response
     */
    public function respond(int $id, string $response_text, int $admin_user_id): bool {
        $sql = "INSERT INTO reclamation_response (reclamation_id, response_text, response_date, responder_id)
                VALUES (:reclamation_id, :response_text, :response_date, :responder_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':reclamation_id' => $id,
            ':response_text'  => $response_text,
            ':response_date'  => date('Y-m-d H:i:s'),
            ':responder_id'   => $admin_user_id
        ]);
    }

    /**
     * Change status on the reclamation table. Set date_resolution if resolved.
     */
    public function changeStatus(int $id, string $status) {
        $fields = ['statut' => $status];
        if ($status === 'résolue') $fields['date_resolution'] = date('Y-m-d H:i:s');
        return $this->update($id, $fields);
    }

    /**
     * Return counts grouped by priority and computed percentages.
     * Example return:
     * [
     *   'low' => 3,
     *   'normal' => 5,
     *   'high' => 2,
     *   'total' => 10,
     *   'percent' => ['low'=>30, 'normal'=>50, 'high'=>20]
     * ]
     */
    public function priorityStats(): array {
        $sql = "SELECT priority, COUNT(*) AS cnt FROM reclamation GROUP BY priority";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $counts = [
            'low' => 0,
            'normal' => 0,
            'high' => 0,
        ];
        $total = 0;
        foreach ($rows as $r) {
            $p = strtolower($r['priority'] ?? '');
            $c = (int)$r['cnt'];
            if ($p === 'low') $counts['low'] = $c;
            elseif ($p === 'normal') $counts['normal'] = $c;
            elseif ($p === 'high') $counts['high'] = $c;
            else {
                // if other priorities exist, group them under 'normal' fallback
                $counts['normal'] += $c;
            }
            $total += $c;
        }

        $percent = [
            'low' => $total > 0 ? round($counts['low'] / $total * 100, 1) : 0,
            'normal' => $total > 0 ? round($counts['normal'] / $total * 100, 1) : 0,
            'high' => $total > 0 ? round($counts['high'] / $total * 100, 1) : 0,
        ];

        return [
            'low' => $counts['low'],
            'normal' => $counts['normal'],
            'high' => $counts['high'],
            'total' => $total,
            'percent' => $percent
        ];
    }
}
