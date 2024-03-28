<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class FetchAllStudents
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

    public function fetchAllStudents($jwt, $queryParams)
    {
        try {
            // Validate JWT token
            $jwtData = JWT::decode($jwt, new Key($this->secretKey, 'HS512'));

            if ($jwtData && isset($jwtData->id)) {
                $id = $jwtData->id;
                $studentId = intval($queryParams['id']);

                if ($id === '1') { // Assuming '1' is the admin ID
                    // Perform deletion
                    $deleteLoginInfoQuery = "DELETE FROM `login_infos` WHERE id = ?";
                    $deleteRegistrationInfoQuery = "DELETE FROM `registration_infos` WHERE id = ?";

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
            } else {
                throw new Exception("Invalid JWT token or missing required data.");
            }
        } catch (Exception $e) {
            $response["status"] = 500;
            $response["msg"] = "Server error: " . $e->getMessage() . " (line: " . $e->getLine() . ")";
            echo json_encode($response);
        }
    }
}
