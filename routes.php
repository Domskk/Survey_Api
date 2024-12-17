<?php
require_once "./config/db.php";
require_once "./modules/Get.php";
require_once "./modules/Post.php";
require_once "./modules/Patch.php";
require_once "./modules/Delete.php";
require_once "./modules/Auth.php";
require_once "./modules/Crypt.php";

$db = new Connection();
$pdo = $db->connect();

// // instantiate classes
$post = new Post($pdo);
// // $patch = new Patch($pdo);
$get = new Get($pdo);
// // $delete = new Delete($pdo);
$auth = new Authentication($pdo);
// // $crypt = new Crypt();

if (isset($_REQUEST['request'])) {
    $request = explode("/", $_REQUEST['request']);
} else {
    echo "URL does not exist.";
}

switch ($_SERVER['REQUEST_METHOD']) {
    case "GET":
        // if ($auth->isAuthorized())
         {
            switch ($request[0]) {
                case "users":
                    echo json_encode($get->getAllUsers());
                    break;
                case "surveys":
                    // echo "Hello";
                    echo json_encode($get->getSurveys($request[1] ?? null));
                    break;
                case "questions":
                    // echo "test";
                    echo json_encode($get->getQuestions($request[1] ?? null));
                    break;
                case "responses":
                    echo json_encode($get->getResponses($request[1] ?? null));
                    break;
                
                default:
                    http_response_code(401);
                    echo "This is invalid endpoint";
                break;
    }
}
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
                default:
                    echo json_encode(["message" => "Invalid Endpoint", "code" => 404]);
                    break;
            }
            break;


//     case "PATCH":
//         $json = json_decode(file_get_contents("php://input"));
//         if ($request[0] == "update-user") {
//             echo json_encode($patch->updateUser($json, $request[1]));
//         }
//         break;

//     case "DELETE":
//         if ($request[0] == "delete-user") {
//             echo json_encode($delete->deleteUser($request[1]));
//         }
//         break;

//     default:
//         echo json_encode(["message" => "Unsupported HTTP method", "code" => 405]);
//         break;
}
?>
