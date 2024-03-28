<?php

require __DIR__."/../vendor/autoload.php";
require_once __DIR__."/../config/ConnectionServices.php";
require_once __DIR__."/../controllers/auth/register.php";
require_once __DIR__."/../controllers/auth/index.php";
require __DIR__. "/../controllers/admin/DeleteStudent.php";

$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'POST':
        if ($_SERVER['PATH_INFO'] == '/login') {
            $userAuth = new UserAuthentication();
            $data = json_decode(file_get_contents("php://input"));
            $userAuth->authenticate($data);
            exit;
        } 
        if ($_SERVER['PATH_INFO'] == '/register') {
            $userAuthOne = new UserRegistration();
            $dataone = json_decode(file_get_contents("php://input"), true);
            $userAuthOne->registerUser($dataone);
            exit;
        } 
        break;
        case 'DELETE':
            if ($_SERVER['PATH_INFO'] == '/deletestudent') {
                $jwt_token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
        
                if ($jwt_token) {
                    $userAuthTwo = new StudentDeletionAPI();
                    $dataTwo = json_decode(file_get_contents("php://input"), true);
                    $userAuthTwo->deleteStudent($jwt_token, $dataTwo);
                    exit;
                } else {
                    http_response_code(401); 
                    echo json_encode(array("message" => "JWT token is missing."));
                    exit;
                }
            } 
            break;   
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method Not Allowed"));
        break;
}

?>
