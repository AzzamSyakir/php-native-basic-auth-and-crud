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

    public function Post()
    {
        $conn = ConnectDB();

        $input = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);
        $name = isset($input['name']) ? $input['name'] : null;
        $password = isset($input['password']) ? $input['password'] : null;
    
        header('Content-Type: application/json');
        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Name must be filled'], JSON_PRETTY_PRINT);
        } elseif (empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be filled'], JSON_PRETTY_PRINT);
        } else {
            $createUser = $this->createUser($conn, $name, $password);
            if ($createUser['status'] === 'success') {
                $conn->commit();
                echo json_encode(['status' => $createUser['status'], 'message' => $createUser['message'], 'data' =>$createUser['data']], JSON_PRETTY_PRINT);
            } else {
                $conn->rollback();
                echo json_encode(['status' => $createUser['status'], 'message' => $createUser['message']], JSON_PRETTY_PRINT);
            }
        }
    }
    private function createUser(mysqli $conn, string $name, string $password): array
    {
        try {
            $dateTime = new DateTime();
            $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
            $id = substr(sha1(time()), 0, 10);

            $query = "INSERT INTO users (id, name, password, created_at, updated_at) VALUES ('$id', '$name', '$password', '$formattedDateTime', '$formattedDateTime')";
            $result = $conn->query($query);

            if ($result) {
                $conn->commit();
                $newUser = [
                    'id' => $id,
                    'name' => $name,
                    'password' => $password,
                    'created_at' => $formattedDateTime,
                    'updated_at' => $formattedDateTime
                ];
                return ['status' => 'success', 'message'=> 'success CreateUser', 'data' => $newUser];
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            return (['status' => 'error', 'message' => "Failed: " . $e->getMessage()]);
        }
    }
}
