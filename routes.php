<?php
require_once "./config/db.php";
require_once "./modules/Get.php";
require_once "./modules/Post.php";
require_once "./modules/Patch.php";
require_once "./modules/Delete.php";
require_once "./modules/Auth.php";


$db = new Connection();
$pdo = $db->connect();

$post = new Post($pdo);
$patch = new Patch($pdo);
$get = new Get($pdo);
$delete = new Delete($pdo);
$auth = new Authentication($pdo);

if (isset($_REQUEST['request'])) {
    $request = explode("/", $_REQUEST['request']);
} else {
    echo "URL does not exist.";
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case "GET":
        switch ($request[0]) {
            case "users":
                if(isset ($request[1])){
                    echo json_encode($get->getUserById($request[1]));
                }else {
                echo json_encode($get->getAllUsers());
                    
                }
                break;
            case "surveys":
                echo json_encode($get->getSurveys($request[1] ?? null));
                break;
            case "questions":
                echo json_encode($get->getQuestions($request[1] ?? null));
                break;
            case "responses":
                echo json_encode($get->getResponses($request[1] ?? null));
                break;
            case "questions-responses":
                echo json_encode($get->getAllQuestionsAndResponses());
                break;
            case "survey-analytics":
                $start_date = $_GET['start_date'] ?? null;
                $end_date = $_GET['end_date'] ?? null;
                echo json_encode($get->getSurveyAnalytics($start_date, $end_date));
                break;
            case "question-analytics":
                $start_date = $_GET['start_date'] ?? null;
                $end_date = $_GET['end_date'] ?? null;
                echo json_encode($get->getQuestionAnalytics($start_date, $end_date));
                break;
            default:
                http_response_code(404);
                echo "This is an invalid endpoint.";
                break;
        }
        break;

    case "POST":
        $body = json_decode(file_get_contents("php://input"));
        if (is_null($body)) {
            echo json_encode(["message" => "Invalid or empty request body.", "code" => 400]);
            break;
        }
        switch ($request[0]) {
            case "login":
                echo json_encode($auth->login($body));
                break;
            case "user":
                echo json_encode($post->postUser($body));
                break;
            case "responses":
                echo json_encode($post->postResponse($body));
                break;
            case "survey":
                echo json_encode($post->postSurvey($body));
                break;
            case "questions":
                echo json_encode($post->postQuestion($body));
                break;
            case "encrypt":
                $body = json_decode(file_get_contents("php://input"), true);
                if (isset($body['data'])) {
                    $response = $crypt->encryptData($body['data']);
                    echo json_encode($response);
                } else {
                    echo json_encode(["message" => "Data is required.", "code" => 400]);
                }
                break;
                
            case "decrypt":
                $body = json_decode(file_get_contents("php://input"), true);
                if (isset($body['data'])) {
                    $response = $crypt->decryptData($body);
                    echo json_encode($response);
                } else {
                    echo json_encode(["message" => "Data is required.", "code" => 400]);
                }
                    break;
                
            default:
                http_response_code(404);
                echo json_encode([
                    "message" => "This is an invalid endpoint: " . ($request[0] ?? "unknown"),
                    "code" => 404
                ]);
                break;
        }
        break;

    case "PATCH":
        $body = json_decode(file_get_contents("php://input"), true);
        if (isset($body['user_id'])) {
            $userId = $body['user_id'];
            $newUsername = $body['username'] ?? null;
            $newPassword = $body['password'] ?? null;

            $response = $patch->updateUser($userId, $newUsername, $newPassword);
            echo json_encode($response);
        } else {
            echo json_encode([
                "payload" => [],
                "message" => "User ID is required.",
                "code" => 400
            ]);
        }
        break;

        case "DELETE":
            $body = json_decode(file_get_contents("php://input"), true);
            
            if (is_null($body)) {
                echo json_encode(["message" => "Invalid or empty request body.", "code" => 400]);
                break;
            }
            
            switch ($request[0]) {
                case "surveys":
                    if (isset($body['survey_id'])) {
                        echo json_encode($delete->deleteSurvey($body['survey_id']));
                    } else {
                        echo json_encode([
                            "message" => "Survey ID is required.",
                            "code" => 400
                        ]);
                    }
                    break;
                    
                case "delete-user":
                    if (isset($body['user_id'])) {
                        echo json_encode($delete->deleteUser($body['user_id']));
                    } else {
                        echo json_encode([
                            "message" => "User ID is required.",
                            "code" => 400
                        ]);
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode([
                        "message" => "This is an invalid endpoint: " . ($request[0] ?? "unknown"),
                        "code" => 404
                    ]);
                    break;
            }
            break;
            
}       
?>
