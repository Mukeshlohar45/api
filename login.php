<?php
$allowedOrigin = 'http://localhost';

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowedOrigin) {
    header("Access-Control-Allow-Origin: $allowedOrigin");
}
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");


use \Firebase\JWT\JWT;
use Dotenv\Dotenv;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\RateLimiter\RateLimiterFactory;
class UserAuthentication
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

   
    public function authenticate($data)
    {
        $factory = new RateLimiterFactory([
            'id' => 'login',
            'policy' => 'token_bucket',
            'limit' => 2, // Number of requests allowed within the given interval
            'rate' => ['interval' => '1 minutes'], // Time interval for the rate limit
        ], new InMemoryStorage());
        // print_r($factory);
        // Create a Rate Limiter instance
        $limiter = $factory->create();
        // var_dump($limiter);
        $limiter->reserve(1)->wait();

        // Attempt to consume a token from the Rate Limiter
        if (!$limiter->consume(1)->isAccepted()) {
            http_response_code(429);
            echo json_encode(["message" => "you have reached login limit . Please try again later."]);
            return;
        }
        if (!empty($data->username) && !empty($data->password)) {
            $username = $data->username;
            $password = $data->password;

            if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(array("message" => "Invalid email format."));
                return;
            }

            $sql = "SELECT * FROM login_infos WHERE (username = ? OR email = ?) LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();

                if (password_verify($password, $row['password'])) {
                    $sid = $row['sid'];
                    $q = "SELECT * FROM registration_infos WHERE id = ?";
                    $stmt2 = $this->db->prepare($q);
                    $stmt2->bind_param("i", $sid);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    $status = $result2->fetch_assoc();

                    if ($status['status'] == "active" && $status['is_varified'] == 'true' && $status['is_approved'] == 'true') {
                        $createdTime = time();
                        $expireTime = $createdTime + 3600;

                        $token = array(
                            "iss" => $this->serverName,
                            "iat" => $createdTime,
                            "exp" => $expireTime,
                            "username" => $row['username'],
                            "id" => $row['sid'],
                        );

                        $jwt = JWT::encode($token, $this->secretKey, 'HS512');

                        http_response_code(200);
                        echo json_encode(array(
                            "message" => "Login successfully.",
                            "status" => "Success",
                            "jwt" => $jwt,
                        ));
                    } else {
                        http_response_code(401);
                        echo json_encode(array("message" => "The user is not authorized."));
                    }
                } else {
                    http_response_code(401);
                    echo json_encode(array("message" => "Invalid credentials."));
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "User not found."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "You are missing username or password."));
        }
    }
}