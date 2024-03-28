<?php

require __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/ConnectionServices.php";
require_once __DIR__ . "/../controllers/auth/register.php";
require_once __DIR__ . "/../controllers/auth/index.php";
require __DIR__ . "/../controllers/admin/DeleteStudent.php";
require __DIR__ . "/../controllers/admin/AllStudents.php";
require __DIR__ . "/../controllers/student.php";
require __DIR__ . "/../controllers/student/updateprofile.php";
require __DIR__ . "/../controllers/admin/ApproveStudent.php";

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
            // echo $jwt_token;
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
    case 'GET':
        if ($_SERVER['PATH_INFO'] === '/allstudents') {
            $jwt_token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
            if ($jwt_token) {
                $userAuthThree = new FetchAllStudents();
                // $dataThree = json_decode(file_get_contents("php://input"), true);

                $userAuthThree->fetchAllStudents($jwt_token);
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "JWT token is missing."));
            }
        } else if ($_SERVER['PATH_INFO'] == '/student') {
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                // var_dump($id);
                $userAuthFour = new FetchStudent();
                $userAuthFour->fetchStudent($id);
                exit;
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Bad Request: Missing 'id' parameter"));
            }
            exit; 
        
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Not Found"));
        }
        break;
    case 'PUT':
        if ($_SERVER['PATH_INFO'] == '/updateprofile') {
            $jwt_token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
            if ($jwt_token) {
                $userAuthFive = new UpdateProfile();
                $dataFive = json_decode(file_get_contents("php://input"), true);
                $userAuthFive->updateProfile($jwt_token, $dataFive);
                exit;
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "JWT token is missing."));
                exit;
            }
        }
        break;
    case 'PATCH':
        if ($_SERVER['PATH_INFO'] == '/approvestudent') {
            try {
                $jwt_token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
                // var_dump($jwt_token);
                if ($jwt_token) {
                    $approveStudent = new ApproveStudent();
                    $data = json_decode(file_get_contents("php://input"), true);
                    // $result = implode("",$data);
                    // echo $result;
                    $approveStudent->approveStudent($jwt_token, $data);
                    exit;
                } else {
                    http_response_code(401);
                    echo json_encode(array("error" => "Missing JWT token"));
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array("error" => $e->getMessage()));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("error" => "Invalid endpoint"));
        }

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method Not Allowed"));
        break;
}
// 