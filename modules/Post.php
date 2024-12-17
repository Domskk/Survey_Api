<?php
include_once "Common.php";
class Post extends Common {
    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function postUser($body) {
        if (!isset($body->username) || !isset($body->password)) {
            return $this->generateResponse(
                null,
                "failed",
                "Username and password are required.",
                400
            );
        }

        if ($body->username === $body->password) {
            return $this->generateResponse(
                null,
                "failed",
                "Password cannot be the same as the username.",
                400
            );
        }

        $sqlString = "SELECT COUNT(*) FROM users_tbl WHERE username = ?";
        $stmt = $this->pdo->prepare($sqlString);
        $stmt->execute([$body->username]);
        $usernameExists = $stmt->fetchColumn();

        if ($usernameExists > 0) {
            return $this->generateResponse(
                null,
                "failed",
                "Username already exists.",
                400
            );
        }

        $hashedPassword = password_hash($body->password, PASSWORD_BCRYPT);

        try {
            $sqlString = "INSERT INTO users_tbl (username, password) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$body->username, $hashedPassword]);

            $user_id = $this->pdo->lastInsertId();
            $user_data = [
                'user_id' => $user_id,
                'username' => $body->username
            ];

            return $this->generateResponse(
                $user_data,
                "success",
                "Successfully created a new user.",
                200
            );
        } catch (\PDOException $e) {
            return $this->generateResponse(
                null,
                "failed",
                $e->getMessage(),
                400
            );
        }
    }
}
?>