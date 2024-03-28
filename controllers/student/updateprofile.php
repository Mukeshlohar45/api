<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class UpdateProfile {
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

    public function updateProfile($jwt, $userData)
    {
        try {
            $jwt = preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches);
            $jwtData = JWT::decode($matches[1], new Key($this->secretKey, 'HS512'));
            
            if ($jwtData && isset($jwtData->id)) {
                $userId = $jwtData->id;
                // var_dump($userId);

            
                $sql = "UPDATE `registration_infos` SET `firstname` = ?, `lastname` = ?, `phonenumber` = ?, `gender` = ?, `hobby` = ?, `grade` = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ssssssi", $userData['firstname'], $userData['lastname'], $userData['phonenumber'], $userData['gender'], $userData['hobby'], $userData['grade'], $userId);
                $result = $stmt->execute();

                if ($result) {
                    echo json_encode(array("message" => "Profile updated successfully"));
                } else {
                    http_response_code(500);
                    throw new Exception("Error updating profile: " . $stmt->error);
                }
            } else {
                throw new Exception("Invalid JWT token or missing user ID");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("error" => $e->getMessage()));
        }
    }
}


?>
