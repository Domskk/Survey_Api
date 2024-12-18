<?php
include_once "Common.php";
include_once "Get.php";  

class Patch extends Common {
    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function updateUser($userId, $newUsername = null, $newPassword = null) {
        $setClauses = [];
        $params = [];
        
        if ($newUsername) {
            $setClauses[] = "username = :newUsername";
            $params[':newUsername'] = $newUsername;
        }
        
        if ($newPassword) {
            $setClauses[] = "password = :newPassword";
            $params[':newPassword'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        if (empty($setClauses)) {
            return $this->generateResponse([], "failed", "No valid fields to update.", 400);
        }

        $setClause = implode(", ", $setClauses);
        $sqlString = "UPDATE users_tbl SET $setClause WHERE user_id = :userId";

        try {
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->generateResponse([], "success", "User updated successfully.", 200);
            } else {
                return $this->generateResponse([], "failed", "No changes were made or user not found.", 404);
            }
        } catch (\PDOException $e) {
            return $this->generateResponse([], "failed", $e->getMessage(), 500);
        }
    }
}