<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../db.php';
require 'vendor/autoload.php';
class UserController
{
    public function get()
    {
        $conn = ConnectDB();
        $conn->begin_transaction();
        $fetchUser = $this->fetchUser($conn);

        header('Content-Type: application/json');
        if (!empty($fetchUser)) {
            echo json_encode(['status' => 'success', 'data' => $fetchUser], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No user found.'], JSON_PRETTY_PRINT);
        }
    }

    private function fetchUser(mysqli $conn): array
    {
        $user = [];
        try {
            $query = "SELECT * FROM users";
            $result = $conn->query($query);

            if ($result) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $user[] = $row;
                    }
                }
                $conn->commit();
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => "Failed: " . $e->getMessage()], JSON_PRETTY_PRINT);
        }

        return $user;
    }

    public function Register()
    {
        $conn = ConnectDB();
        $conn->begin_transaction();
    
        $input = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);
        $username = isset($input['username']) ? $input['username'] : null;
        $password = isset($input['password']) ? $input['password'] : null;
        $email = isset($input['email']) ? $input['email'] : null;
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16));
    
        header('Content-Type: application/json');
    
        if (empty($username)) {
            echo json_encode(['status' => 'error', 'message' => 'Name must be filled'], JSON_PRETTY_PRINT);
            return;
        }
    
        if (empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be filled'], JSON_PRETTY_PRINT);
            return;
        }
    
        try {
            $id = substr(sha1(time()), 0, 10);
            $query = "INSERT INTO users (id, username, password, email, token, confirmed) VALUES (?, ?, ?, ?, ?, false)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssss", $id, $username, $password_hash, $email, $token);
            
            if (!$stmt->execute()) {
                $conn->rollback();
                throw new Exception($stmt->error);
            }
    
            // Send email
            $mail = new PHPMailer(true);
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
            $dotenv->load();
    
            $appHost = $_ENV['APP_HOST'];
            $senderEmailAddress = $_ENV['SENDER_EMAIL_ADDRESS'];
            $senderEmailPassword = $_ENV['SENDER_EMAIL_PASSWORD'];
            $activation_link = $appHost . "/confirm/" . $token;
    
            // Server settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = $senderEmailAddress;
            $mail->Password = $senderEmailPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
    
            // Recipients
            $mail->setFrom($senderEmailAddress, 'no-reply');
            $mail->addAddress($email);
    
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Please activate your account';
            $mail->Body = "Hi,<br>Please click the following link to activate your account:<br><a href='$activation_link'>$activation_link</a>";
    
            $mail->send();
    
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Register Success'], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => "Failed: " . $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }
    
}