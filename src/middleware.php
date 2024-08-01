<?php
require 'model/sessions.php';

class Middleware {
    public function ValidateToken(mysqli $conn)
{
    $accessToken = $this->GetTokenfromHeader();
    if (empty($accessToken)) {
        echo json_encode(['status' => 'error', 'message' => 'Authorize failed : Authorization header not found or empty'], JSON_PRETTY_PRINT);
        return;
    }

    try {
        if ($conn->begin_transaction() === false) {
            throw new Exception("Failed to start transaction: " . $conn->error);
        }

        // validate token
        $query = "SELECT * FROM sessions WHERE access_token=?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param('s', $accessToken);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $conn->error);
        }

        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $session = new Session(
                $row['id'],
                $row['user_id'],
                $row['access_token'],
                $row['refresh_token'],
                $row['access_token_expired_at'],
                $row['refresh_token_expired_at']
            );

            $dateTime = new DateTime();
            $timeNow = $dateTime->format('Y-m-d H:i:s');
            if ($session->accessTokenExpiredAt <= $timeNow) {
                throw new Exception("Token expired");
            }
        } else {
            throw new Exception("Invalid token");
        }
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => "Authorize Failed: " . $e->getMessage()], JSON_PRETTY_PRINT);
        return;
    }
}
public function GetTokenfromHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    $token = null;
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            $token = $matches[1];
        }
    }
    return $token;
}

}