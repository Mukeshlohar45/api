<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class StudentDeletionAPI
{
    private $db;
    private $secretKey;
    private $serverName;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();

        $this->serverName = $_ENV['SERVERNAME'];
        $database = new Database();
        $this->secretKey = $_ENV['JWT_SECRET'];
        $this->db = $database->getconnect();
    }

    public function deleteStudent($jwt, $dataTwo)
    {
        try {
            $jwt = preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches);
            $jwtData = JWT::decode($matches[1], new Key($this->secretKey, 'HS512'));
            var_dump($jwt);
            if ($jwtData && isset($jwtData->id)) {
                $id = $jwtData->id;
                // var_dump($id);
                //  var_dump($dataTwo['id']);
                if ('1' == $id) {
                    $deleteLoginInfoQuery = "DELETE FROM `login_infos` WHERE sid = ?";
                    $deleteRegistrationInfoQuery = "DELETE FROM `registration_infos` WHERE id = ?";
                    $studentId = intval($dataTwo['id']);


                    $stmt2 = $this->db->prepare($deleteLoginInfoQuery);
                    $stmt2->bind_param("i", $studentId);
                    $stmt2->execute();

                    $stmt3 = $this->db->prepare($deleteRegistrationInfoQuery);
                    $stmt3->bind_param("i", $studentId);
                    $stmt3->execute();
                    $stmt2->close();
                    $stmt3->close();
                    $response["msg"] = "Student data deleted";
                    $response["status"] = 200;
                    echo json_encode($response);
                    return;
                } else {
                    throw new Exception("Only admin can delete student.");
                }
                // } else {
                //     throw new Exception("Unauthorized access. Admin role required.");
                // }
            } else {
                throw new Exception("Invalid JWT token or missing required data.");
            }
        } catch (Exception $e) {
            $response["status"] = 500;
            $response["msg"] = "Server error: " . $e->getMessage() . " (line: " . $e->getLine() . ")";
            echo json_encode($response);
        }
    }

    private function deleteStudentRecord($id)
    {
    }
}
