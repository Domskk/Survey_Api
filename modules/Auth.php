<?php
class Authentication {
    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function isAuthorized() {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        return $this->getToken() === ($headers['authorization'] ?? null);
    }

    private function getToken() {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        if (!isset($headers['x-auth-user'])) {
            return "";
        }

        $sqlString = "SELECT token FROM users_tbl WHERE username = ?";
        try {
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$headers['x-auth-user']]);
            $result = $stmt->fetch();
            return $result['token'] ?? "";
        } catch (\PDOException $e) {
            return "";
        }
    }

    private function generateHeader() {
        $header = [
            "typ" => "JWT",
            "alg" => "HS256",
            "app" => "SurveyHubs",
            "dev" => "Jaztine Dominick Reyes"
        ];
        return base64_encode(json_encode($header));
    }

    private function generatePayload($id, $username) {
        $payload = [
            "uid" => $id,
            "uc" => $username,
            "email" => "jaztinereyes@gmail.com",
            "date" => date("Y-m-d H:i:s"),
            "exp" => date("Y-m-d H:i:s", strtotime("+1 hour"))
        ];
        return base64_encode(json_encode($payload));
    }

    private function generateToken($id, $username) {
        $header = $this->generateHeader();
        $payload = $this->generatePayload($id, $username);
        $signature = hash_hmac("sha256", "$header.$payload", TOKEN_KEY, true);
        return "$header.$payload." . base64_encode($signature);
    }

    public function saveToken($token, $username) {
        try {
            $sqlString = "UPDATE users_tbl SET token = ? WHERE username = ?";
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$token, $username]);
            return ["data" => null, "code" => 200];
        } catch (\PDOException $e) {
            return ["errmsg" => $e->getMessage(), "code" => 400];
        }
    }

    private function encryptPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function login($body) {
        $username = $body->username ?? null;
        $password = $body->password ?? null;

        if (!$username || !$password) {
            return [
                "payload" => null,
                "remarks" => "failed",
                "message" => "Username and password are required.",
                "code" => 400
            ];
        }

        try {
            $sqlString = "SELECT user_id, username, password, token FROM users_tbl WHERE username = ?";
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$username]);

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch();
                if ($this->isSamePassword($password, $result['password'])) {
                    $token = $this->generateToken($result['user_id'], $result['username']);
                    $tokenArr = explode('.', $token);
                    $this->saveToken($tokenArr[2], $result['username']);
                    return [
                        "payload" => [
                            "user_id" => $result['user_id'],
                            "username" => $result['username'],
                            "token" => $tokenArr[2]
                        ],
                        "remarks" => "success",
                        "message" => "Logged in successfully.",
                        "code" => 200
                    ];
                } else {
                    return [
                        "payload" => null,
                        "remarks" => "failed",
                        "message" => "Incorrect password.",
                        "code" => 401
                    ];
                }
            } else {
                return [
                    "payload" => null,
                    "remarks" => "failed",
                    "message" => "Username does not exist.",
                    "code" => 401
                ];
            }
        } catch (\PDOException $e) {
            return [
                "payload" => null,
                "remarks" => "failed",
                "message" => $e->getMessage(),
                "code" => 400
            ];
        }
    }

    public function postUser($body) {
        $username = $body->username ?? null;
        $password = $body->password ?? null;

        if (!$username || !$password) {
            return [
                "errmsg" => "Username and password are required.",
                "code" => 400
            ];
        }

        $passwordHash = $this->encryptPassword($password);
        try {
            $sqlString = "INSERT INTO users_tbl (username, password) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$username, $passwordHash]);
            return ["data" => null, "code" => 200];
        } catch (\PDOException $e) {
            return ["errmsg" => $e->getMessage(), "code" => 400];
        }
    }

    private function isSamePassword($inputPassword, $storedPassword) {
        return password_verify($inputPassword, $storedPassword);
    }
}

?>
