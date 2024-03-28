<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class FetchAllStudents {
    private $db;
    private $secretKey;

    public function __construct() 
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();

        $database = new Database(); 
        $this->secretKey = $_ENV['JWT_SECRET']; 
        $this->db = $database->getconnect(); 
    }

    public function fetchAllStudents($jwt_token) 
    {
        try {
            // $matches = [];
            preg_match('/Bearer\s(\S+)/', $jwt_token, $matches);
            if (!isset($matches[1])) {
                throw new Exception("Invalid JWT token");
            }
            
            $jwtData = JWT::decode($matches[1], new Key($this->secretKey, 'HS512'));
            // var_dump($jwtData);

            if ($jwtData && isset($jwtData->id)) {
                $id = $jwtData->id;
        
                if ('1' == $id) {
                    $data = [];
                    $query = "SELECT * FROM `registration_infos`";

                    $result = mysqli_query($this->db, $query);

                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            $data[] = $row;
                        }
                        echo json_encode($data);
                    } else {
                        http_response_code(404);
                        throw new Exception("Data not found");
                    }
                } else {
                    http_response_code(400);
                    throw new Exception("Unauthorized access. only admin can see all students");
                }
            } else {
                http_response_code(400);
                throw new Exception("Unauthorized access");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => "Server error: " . $e->getMessage() . " (line: " . $e->getLine() . ")"));
        }
    }
}

?>
