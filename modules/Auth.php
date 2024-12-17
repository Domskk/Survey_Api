<?php
// class Authentication{

//     protected $pdo;
    
//     public function __construct(\PDO $pdo){
//         $this->pdo = $pdo;
//     }

//     public function isAuthorized(){
//         $headers = array_change_key_case(getallheaders(),CASE_LOWER);
//         return $this->getToken() === $headers['authorization'];
//     }

//     private function getToken(){
//         $headers = array_change_key_case(getallheaders(),CASE_LOWER);

//         $sqlString = "SELECT token FROM users WHERE username=?";
//         try{
//             $stmt = $this->pdo->prepare($sqlString);
//             $stmt->execute([$headers['x-auth-user']]);
//             $result = $stmt->fetchAll()[0];
//             return $result['token'];
//         }
//         catch(Exception $e){
//             echo $e->getMessage();
//         }
//         return "";
//     }

//     private function generateHeader(){
//         $header = [
//             "typ" => "JWT",
//             "alg" => "HS256",
//             "app" => "SurveyHubs",
//             "dev" => "Jaztine Dominick Reyes"
//         ];
//         return base64_encode(json_encode($header));
//     }

//     private function generatePayload($id, $username){
//         $payload = [
//             "uid" => $id,
//             "uc" => $username,
//             "email" => "jaztinereyes@gmail.com",
//             "date" => date_create(),
//             "exp" => date("Y-m-d H:i:s")
//         ];
//         return base64_encode(json_encode($payload));
//     }

//     private function generateToken($id, $username){
//         $header = $this->generateHeader();
//         $payload = $this->generatePayload($id, $username);
//         $signature = hash_hmac("sha256", "$header.$payload", TOKEN_KEY);
//         return "$header.$payload." . base64_encode($signature);
//     }

//     public function saveToken($token, $username){
//         $errmsg = "";
//         $code = 0;
        
//         try{
//             $sqlString = "UPDATE users SET token=? WHERE username = ?";
//             $sql = $this->pdo->prepare($sqlString);
//             $sql->execute( [$token, $username] );

//             $code = 200;
//             $data = null;

//             return array("data"=>$data, "code"=>$code);
//         }
//         catch(\PDOException $e){
//             $errmsg = $e->getMessage();
//             $code = 400;
//         }

        
//         return array("errmsg"=>$errmsg, "code"=>$code);
//     }

//     public function login($body){
//         $username = $body->username;
//         $password = $body->password;

//         $code = 0;
//         $payload = "";
//         $remarks = "";
//         $message = "";

//         try{
//             $sqlString = "SELECT users_id, username, password, token FROM users WHERE username=?";
//             $stmt = $this->pdo->prepare($sqlString);
//             $stmt->execute([$username]);

//             if($stmt->rowCount() > 0){
//                 $result = $stmt->fetchAll()[0];
//                 if($this->isSamePassword($password, $result['password'])){
//                     $code = 200;
//                     $remarks = "success";
//                     $message = "Logged in successfully";

//                     $token = $this->generateToken($result['users_id'], $result['username']);
//                     $token_arr = explode('.', $token);
//                     $this->saveToken($token_arr[2], $result['username']);
//                     $payload = array("users_id"=>$result['users_id'], "username"=>$result['username'], "token"=>$token_arr[2]);
//                 }
//                 else{
//                     $code = 401;
//                     $payload = null;
//                     $remarks = "failed";
//                     $message = "Incorrect Password.";
//                 }
//             }
//             else{
//                 $code = 401;
//                 $payload = null;
//                 $remarks = "failed";
//                 $message = "Username does not exist.";
//             }
//         }
//         catch(\PDOException $e){
//             $message = $e->getMessage();
//             $remarks = "failed";
//             $code = 400;
//         }
//         return array("payload"=>$payload, "remarks"=>$remarks, "message"=>$message, "code"=>$code);
//     }

//     public function addAcc($body){
//         $values = [];
//         $errmsg = "";
//         $code = 0;

//         $body->password = $this->encryptPassword($body->password);

//         foreach($body as $value){
//             array_push($values, $value);
//         }
        
//         try{
//             $sqlString = "INSERT INTO users(users_id, username, password) VALUES (?,?,?)";
//             $sql = $this->pdo->prepare($sqlString);
//             $sql->execute($values);

//             $code = 200;
//             $data = null;

//             return array("data"=>$data, "code"=>$code);
//         }
//         catch(\PDOException $e){
//             $errmsg = $e->getMessage();
//             $code = 400;
//         }

        
//         return array("errmsg"=>$errmsg, "code"=>$code);
//     }
// }
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
