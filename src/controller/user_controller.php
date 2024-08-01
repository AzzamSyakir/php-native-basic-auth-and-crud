<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../db.php';
require 'vendor/autoload.php';
require_once __DIR__ . '/../model/users.php';
class UserController
{
    public function Register(mysqli $conn)
    {
        $conn->begin_transaction();
    
        $input = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);
        $username = isset($input['username']) ? $input['username'] : null;
        $password = isset($input['password']) ? $input['password'] : null;
        $email = isset($input['email']) ? $input['email'] : null;
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16));

    
        header('Content-Type: application/json');

        if (empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'email must be filled'], JSON_PRETTY_PRINT);
            return;
        }

        if (empty($username)) {
            echo json_encode(['status' => 'error', 'message' => 'username must be filled'], JSON_PRETTY_PRINT);
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
            if ($stmt === false) {
                $error_message = "Failed to prepare update statement: " . mysqli_error($conn);
                error_log($error_message);
                throw new Exception($error_message);
            }
            $stmt->bind_param("sssss", $id, $username, $password_hash, $email, $token);
            $status = $stmt->execute();
            if ($status ==false) {
                $conn->rollback();
                throw new Exception($stmt->error);
            }
    
            // Send email
            $mail = new PHPMailer(true);
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
            $dotenv->load();
            $appHost = $_ENV['APP_HOST'];
            $appPort = $_ENV['APP_PORT'];
            $senderEmailAddress = $_ENV['SENDER_EMAIL_ADDRESS'];
            $senderEmailPassword = $_ENV['SENDER_EMAIL_PASSWORD'];
            $activation_link = "http://" . $appHost . ":" . $appPort . "/confirm/" . $token;
    
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
            $mail->Body = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Activate Your Account</title>
                </head>
                <body>
                    <p>Hi,</p>
                    <p>Please click the following link to activate your account:</p>
                    <p><a href=\"$activation_link\">activate link</a></p>
                </body>
                </html>
            ";
            $mail->send();
    
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Register Success'], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => "Failed: " . $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }
    public function ConfirmEmail(mysqli $conn, string $token)
    {
        $conn->begin_transaction();
        
        if ($conn->connect_error) {
            echo json_encode(['status' => 'error', 'message' => "Connection failed: " . $conn->connect_error], JSON_PRETTY_PRINT);
            return;
        }
    
        $conn->begin_transaction();
        try {
            $query = "SELECT * FROM users WHERE token=?";
            $stmt = $conn->prepare($query);
    
            if ($stmt === false) {
                throw new Exception($conn->error);
            }
    
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $user = null;
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user = new User($row['id'], $row['username'], $row['email'], $row['password'], $row['token'],  $row['confirmed']);
            } else {
                throw new Exception("No user found with the given ID.");
            }
    
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => "Get User Failed: " . $e->getMessage()], JSON_PRETTY_PRINT);
            return;
        }
    
        if (!empty($user)) {
            try {
                $user->confirmed = true;
                $stmt = $conn->prepare("UPDATE users SET confirmed=? WHERE id=?");
                if ($stmt === false) {
                    $error_message = "Failed to prepare update statement: " . mysqli_error($conn);
                    error_log($error_message);
                    throw new Exception($error_message);
                }

                $stmt->bind_param('ss', $user->confirmed, $user->id);
                $status = $stmt->execute();
                if ($status != false) {
                    $conn->commit();
                    echo ('Confirm Email Success');
                } else {
                    throw new Exception("No rows affected. Update might have failed.");
                }
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => "Confirm Email: " . $e->getMessage()], JSON_PRETTY_PRINT);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No user found.'], JSON_PRETTY_PRINT);
        }
    }
    public function Login(mysqli $conn)
    {
        header('Content-Type: application/json');
        
        $conn->begin_transaction();
        
        $input = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        
        if (empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Email must be filled'], JSON_PRETTY_PRINT);
            return;
        }
        
        if (empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be filled'], JSON_PRETTY_PRINT);
            return;
        }
        
        try {
            $query = "SELECT id, password, confirmed FROM users WHERE email=?";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
        
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement: " . $conn->error);
            }
    
            $result = $stmt->get_result();
        
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $userId = $row['id'];
                $password_hash = $row['password'];
                $confirmed = $row['confirmed'];
            } else {
                throw new Exception("No user found with the given email.");
            }
        
            if (!password_verify($password, $password_hash)) {
                throw new Exception("Password does not match.");
            }
        
            if ($confirmed == 0) {
                throw new Exception("Email not verified. You must verify your email first.");
            }
        
            $dateTime = new DateTime();
            $accessTokenExpiry = (clone $dateTime)->modify('+15 minutes');
            $refreshTokenExpiry = (clone $dateTime)->modify('+7 days');
            $accessTokenExpiredAt = $accessTokenExpiry->format('Y-m-d H:i:s');
            $refreshTokenExpiredAt = $refreshTokenExpiry->format('Y-m-d H:i:s');
        
            $accessToken = bin2hex(random_bytes(16));
            $refreshToken = bin2hex(random_bytes(16));
        
            $query = "SELECT * FROM sessions WHERE user_id=?";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
        
            $stmt->bind_param("s", $userId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement: " . $conn->error);
            }
            
            $result = $stmt->get_result();
        
            if ($result && $result->num_rows > 0) {
                $query = "UPDATE sessions SET access_token=?, refresh_token=?, access_token_expired_at=?, refresh_token_expired_at=? WHERE user_id=?";
                $stmt = $conn->prepare($query);
                if ($stmt === false) {
                    throw new Exception("Failed to prepare statement: " . $conn->error);
                }
                $stmt->bind_param("sssss", $accessToken, $refreshToken, $accessTokenExpiredAt, $refreshTokenExpiredAt, $userId);
            } else {
                $id = substr(sha1(time()), 0, 10);
                $query = "INSERT INTO sessions (id, user_id, access_token, refresh_token, access_token_expired_at, refresh_token_expired_at) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                if ($stmt === false) {
                    throw new Exception("Failed to prepare statement: " . $conn->error);
                }
                $stmt->bind_param("ssssss", $id, $userId, $accessToken, $refreshToken, $accessTokenExpiredAt, $refreshTokenExpiredAt);
            }
        
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement: " . $stmt->error);
            }
    
            $conn->commit();
    
            setcookie("access_token_cookie", $accessToken, [
                'expires' => $accessTokenExpiry->getTimestamp(),
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            setcookie("refresh_token_cookie", $refreshToken, [
                'expires' => $refreshTokenExpiry->getTimestamp(),
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
    
            echo json_encode(['status' => 'success', 'message' => 'Login Success'], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => "Login Failed: " . $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }
    
    public function Logout(mysqli $conn) 
    {
        $middleware = new Middleware;
        $accessToken = $middleware->GetTokenfromHeader();
        if (empty($accessToken)) {
            echo json_encode(['status' => 'error', 'message' => 'Logout failed : Authorization header not found or empty'], JSON_PRETTY_PRINT);
            return;
        }
    
        try {
            if ($conn->begin_transaction() === false) {
                throw new Exception("Failed to start transaction: " . $conn->error);
            }
    
            $query = "DELETE FROM sessions WHERE access_token=?";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
    
            $stmt->bind_param('s', $accessToken);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement: " . $conn->error);
            }
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => "Logout Success"], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => "Logout Failed: " . $e->getMessage()], JSON_PRETTY_PRINT);
            return;
        }
    }
    public function GenerateAccToken(mysqli $conn) 
    {
        $middleware = new Middleware();
        $refreshToken = $middleware->GetTokenfromHeader();
    
        if (empty($refreshToken)) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Generate Token failed: Authorization header not found or empty'
            ], JSON_PRETTY_PRINT);
            return;
        }
    
        try {
            if (!$conn->begin_transaction()) {
                throw new Exception("Failed to start transaction: " . $conn->error);
            }
    
            $newAccessToken = bin2hex(random_bytes(16));
            $newRefreshToken = bin2hex(random_bytes(16));
    
            $dateTime = new DateTime();
            $accessTokenExpiry = (clone $dateTime)->modify('+15 minutes');
            $refreshTokenExpiry = (clone $dateTime)->modify('+7 days');
    
            $newAccessTokenExpiredAt = $accessTokenExpiry->format('Y-m-d H:i:s');
            $newRefreshTokenExpiredAt = $refreshTokenExpiry->format('Y-m-d H:i:s');
    
            $query = "UPDATE sessions SET 
                        access_token=?, 
                        access_token_expired_at=?, 
                        refresh_token=?, 
                        refresh_token_expired_at=? 
                      WHERE refresh_token=?";
            $stmt = $conn->prepare($query);
    
            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
    
            $stmt->bind_param(
                'sssss', 
                $newAccessToken, 
                $newAccessTokenExpiredAt, 
                $newRefreshToken, 
                $newRefreshTokenExpiredAt, 
                $refreshToken
            );
    
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement: " . $stmt->error);
            }
    
            $conn->commit();
    
            setcookie("access_token_cookie", $newAccessToken, [
                'expires' => $accessTokenExpiry->getTimestamp(), 
                'secure' => true, 
                'httponly' => true, 
                'samesite' => 'Strict'
            ]);
            setcookie("refresh_token_cookie", $newRefreshToken, [
                'expires' => $refreshTokenExpiry->getTimestamp(), 
                'secure' => true, 
                'httponly' => true, 
                'samesite' => 'Strict'
            ]);
    
            echo json_encode([
                'status' => 'success', 
                'message' => "Generate Token Success"
            ], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'status' => 'error', 
                'message' => "GenerateAccToken Failed: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }
    
    public function hello() {
        echo("hello after login");
    }
    
}