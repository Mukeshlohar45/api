<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class StudentDeletionAPI {
    private $db;
    private $secretKey;
    private $serverName;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();

        $this->serverName = $_ENV['SERVERNAME'];
        $database = new Database();
        $this->secretKey = $_ENV['JWT_SECRET'];
        $this->db = $database->getconnect();
    }

    public function deleteStudent($jwt, $dataTwo) {
        try {
            $jwt = preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches);
            // Validate JWT token
            $jwtData = JWT::decode($matches[1], new Key($this->secretKey, 'HS512'));
            // var_dump($jwtData);
            // var_dump($jwtData);
            if ($jwtData && isset($jwtData->id)) {
                $id = $jwtData->id;
                // $role = $jwtData->role;
                var_dump($id);
                var_dump($dataTwo['id']);
                if ('1' == $id) {
                        // Debugging: Print a message to confirm the deletion process
                        echo "Deleting student with ID: " . $dataTwo['id'];

                        // Proceed with deletion
                        //$this->deleteStudentRecord($dataTwo['id']);

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

    private function deleteStudentRecord($id) {
        $deleteVerifiedEmailsQuery = "DELETE FROM `verified_emails` WHERE sid = ?";
        $deleteLoginInfoQuery = "DELETE FROM `login_infos` WHERE sid = ?";
        $deleteRegistrationInfoQuery = "DELETE FROM `registration_infos` WHERE id = ?";

        $stmt1 = $this->db->prepare($deleteVerifiedEmailsQuery);
        $stmt1->bind_param("i", $id);
        $stmt1->execute();

        $stmt2 = $this->db->prepare($deleteLoginInfoQuery);
        $stmt2->bind_param("i", $id);
        $stmt2->execute();

        $stmt3 = $this->db->prepare($deleteRegistrationInfoQuery);
        $stmt3->bind_param("i", $id);
        $stmt3->execute();

        $stmt1->close();
        $stmt2->close();
        $stmt3->close();
    }
}

?>
