<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

class UserRegistration
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getconnect();
    }

    public function registerUser($data)
    {
        try {
            $this->validateRequiredFields($data);

            $firstname = $data['firstname'];
            $lastname = $data['lastname'];
            $email = $data['email'];
            $password = $data['password'];
            $cpassword = $data['cpassword'];
            $phonenumber = $data['phonenumber'];
            $gender = $data['gender'];
            $hobby = $data['hobby'];
            $message = $data['message'];
            $grade = $data['grade'];

            $ency_pass = password_hash($password, PASSWORD_DEFAULT);

            // Implode hobby array
            $mul_hobby = implode(",", $hobby);

            $target_dir = "./../uploads/"; 
            $file_name = $target_dir . $firstname . "_" . $lastname . ".jpg";
            file_put_contents($file_name, base64_decode($data["profile"]));
                   
            $query = "INSERT INTO `registration_infos`( `firstname`, `lastname`, `phonenumber`, `gender`, `hobby`, `message`, `grade`, 'profile') VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssssssss", $firstname, $lastname, $phonenumber, $gender, $mul_hobby, $message, $grade, $file_name);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $sid = $stmt->insert_id;

                $atr = explode("@", $email);
                $username = $atr[0];

                $loginq = "INSERT INTO `login_infos`( `username`, `email`, `password`, `sid`) VALUES (?, ?, ?, ?)";
                $loginstmt = $this->db->prepare($loginq);
                $loginstmt->bind_param("sssi", $username, $email, $ency_pass, $sid);
                $loginstmt->execute();

                if ($loginstmt->affected_rows > 0) {
                    $token = bin2hex(random_bytes(12));

                    $storeEmail = "INSERT INTO `varified_emails`(`email`, `token`, `sid`) VALUES (?, ?, ?)";
                    $storestmt = $this->db->prepare($storeEmail);
                    $storestmt->bind_param("ssi", $email, $token, $sid);
                    $storestmt->execute();

                    // $this->db->close();

                    $response = array(
                        "message" => "Student Registered successfully.",
                        "status" => "Success",
                        "firstname" => $firstname,
                        "lastname" => $lastname,
                        "email" => $email,
                        "phonenumber" => $phonenumber,
                        "gender" => $gender,
                        "hobby" => $hobby,
                        "message" => $message,
                        "grade" => $grade
                    );
                    header('Content-Type: application/json');
                    echo json_encode($response);
                } else {
                    throw new Exception("Error inserting login data");
                }
            } else {
                throw new Exception("Error inserting data in table");
            }
        } catch (Exception $e) {
            error_log("Registration failed: " . $e->getMessage()); 
            return array('error' => $e->getMessage()); 
        }
    }

    private function validateRequiredFields($data) {
        $required_fields = ['firstname', 'lastname', 'email', 'password', 'cpassword', 'phonenumber', 'gender', 'hobby', 'message', 'grade'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
    }
}

?>
