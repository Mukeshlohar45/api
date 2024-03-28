<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PATCH");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class ApproveStudent {
    private $db;
    private $secretKey;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();

        $database = new Database();
        $this->db = $database->getconnect();
        $this->secretKey = $_ENV['JWT_SECRET'];
    }

    public function approveStudent($jwt, $userData)
    {
        try {
            $jwtData = JWT::decode($jwt, new Key($this->secretKey, 'HS512'));
            if ($jwtData && isset($jwtData->role) && $jwtData->id === '1') {
                $userId = $jwtData->id;
            
                $sql = "UPDATE `registration_infos` SET `is_approved` = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("si", $userData['is_approved'], $userId);
                $result = $stmt->execute();

                if ($result) {
                    echo json_encode(array("message" => "Student approved successfully"));
                } else {
                    http_response_code(500);
                    throw new Exception("Error in approving student: " . $stmt->error);
                }
            } else {
                // User is not authorized
                http_response_code(403);
                throw new Exception("Unauthorized access");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("error" => $e->getMessage()));
        }
    }
}

?>
