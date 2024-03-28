<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

use Dotenv\Dotenv;

class FetchStudent
{
    private $db;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        $database = new Database();
        $this->db = $database->getconnect();
    }

    public function fetchStudent($id)
    {
        try {
            $data = [];
            $query = "SELECT * FROM `registration_infos` WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            // $sid = intval($id);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $data = $result->fetch_assoc();
                echo json_encode($data);
            } else {
                http_response_code(404);
                throw new Exception("Data not found");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => "error: " . $e->getMessage()));
        }
    }
}
