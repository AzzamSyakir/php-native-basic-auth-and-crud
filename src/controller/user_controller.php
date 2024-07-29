<?php
require_once __DIR__ . '/../db.php';

class UserController
{
    public function get()
    {
        $conn = ConnectDB();
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

        $input = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);
        $username = isset($input['username']) ? $input['username'] : null;
        $password = isset($input['password']) ? $input['password'] : null;
        $email = isset($input['email']) ? $input['email'] : null;
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        header('Content-Type: application/json');
        if (empty($username)) {
            echo json_encode(['status' => 'error', 'message' => 'Name must be filled'], JSON_PRETTY_PRINT);
        } elseif (empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be filled'], JSON_PRETTY_PRINT);
        } else {
            try {
                $id = substr(sha1(time()), 0, 10);
    
                $query = "INSERT INTO users (id, username, password, email, confirmed) VALUES ('$id', '$username', '$password_hash', '$email', false)";
                $result = $conn->query($query);
    
                if ($result) {
                    $conn->commit();
                    echo json_encode(['status' => 'success', 'message'=> 'Register Success'], JSON_PRETTY_PRINT);
                } else {
                    throw new Exception($conn->error);
                }
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => "Failed: " . $e->getMessage()], JSON_PRETTY_PRINT);
            }
        }
    }
}