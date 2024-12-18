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
    public function postResponse($body) {
        if (!isset($body->question_id) || !isset($body->response_text)) {
            return $this->generateResponse(
                null,
                "failed",
                "Question ID and response text are required.",
                400
            );
        }

        try {
            $sqlString = "INSERT INTO responses_tbl (question_id, response_text, created_at) VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($sqlString);

            $created_at = date("Y-m-d H:i:s");
            $stmt->execute([$body->question_id, $body->response_text, $created_at]);

            $response_id = $this->pdo->lastInsertId();

            $questionSql = "SELECT question_id, question_text FROM questions_tbl WHERE question_id = ?";
            $stmt = $this->pdo->prepare($questionSql);
            $stmt->execute([$body->question_id]);
            $questionData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$questionData) {
                return $this->generateResponse(
                    null,
                    "failed",
                    "Invalid question ID provided.",
                    400
                );
            }

            $response_data = [
                'response_id' => $response_id,
                'question_id' => $questionData['question_id'],
                'question_text' => $questionData['question_text'],
                'response_text' => $body->response_text,
                'created_at' => $created_at
            ];

            return $this->generateResponse(
                $response_data,
                "success",
                "Response successfully added.",
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
    public function postSurvey($body) {
        if (!isset($body->title) || !isset($body->user_id)) {
            return $this->generateResponse(
                null,
                "failed",
                "Survey title and user ID are required.",
                400
            );
        }
    
        try {
            $sqlString = "INSERT INTO surveys_tbl (title, user_id, created_at) VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($sqlString);
    
            $created_at = date("Y-m-d H:i:s");
            $stmt->execute([$body->title, $body->user_id, $created_at]);
    
            $survey_id = $this->pdo->lastInsertId();
            $survey_data = [
                'survey_id' => $survey_id,
                'title' => $body->title,
                'user_id' => $body->user_id,
                'created_at' => $created_at
            ];
    
            return $this->generateResponse(
                $survey_data,
                "success",
                "Survey successfully created.",
                200
            );
        } catch (\PDOException $e) {
            return $this->generateResponse(
                null,
                "failed",
                $e->getMessage(),
                500
            );
        }
    }
    
    public function postQuestion($body) {
            if (!isset($body->survey_id) || !isset($body->question_text)) {
                return $this->generateResponse(
                    null,
                    "failed",
                    "Survey ID and question text are required.",
                    400
                );
            }
        
            try {
                $sqlString = "INSERT INTO questions_tbl (survey_id, question_text) VALUES (?, ?)";
                $stmt = $this->pdo->prepare($sqlString);
                $stmt->execute([$body->survey_id, $body->question_text]);
        
                $question_id = $this->pdo->lastInsertId();
                $question_data = [
                    'question_id' => $question_id,
                    'survey_id' => $body->survey_id,
                    'question_text' => $body->question_text
                ];
        
                return $this->generateResponse(
                    $question_data,
                    "success",
                    "Question successfully created.",
                    200
                );
            } catch (\PDOException $e) {
                return $this->generateResponse(
                    null,
                    "failed",
                    $e->getMessage(),
                    500
                );
            }
        }
        
    
}


?>