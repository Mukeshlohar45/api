<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

class Database
{
    private $serverName;
    private $userName;
    private $password;
    private $databaseName;

    function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        $this->serverName = $_ENV['DB_HOST'];
        $this->userName = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
        $this->databaseName = $_ENV['DB_NAME'];
    }

    function getconnect()
    {
        try {
            $conn = new mysqli($this->serverName, $this->userName, $this->password, $this->databaseName);

            if ($conn->connect_error) {
                throw new Exception("Failed to connect: " . $conn->connect_error);
            }
            return $conn;
        } catch (Exception $e) {
            throw new Exception("Connection error: " . $e->getMessage());
        }
    }
}
?>
