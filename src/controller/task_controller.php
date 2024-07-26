<?php
require_once __DIR__ . '/../db.php';

class TaskController
{
    public function get()
    {
        $conn = ConnectDB();
        $fetchTask = $this->fetchTask($conn);

        header('Content-Type: application/json');
        if (!empty($fetchTask)) {
            echo json_encode(['status' => 'success', 'data' => $fetchTask], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No task found.'], JSON_PRETTY_PRINT);
        }
    }

    private function fetchTask(mysqli $conn): array
    {
        $task = [];
        try {
            $query = "SELECT * FROM tasks";
            $result = $conn->query($query);

            if ($result) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $task[] = $row;
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

        return $task;
    }

    public function post()
    {
        $conn = ConnectDB();
        if (!empty($_POST)){
          $title = $_POST['title'];
          $completed = $_POST['completed'];
          }
          else {
              $_POST = json_decode(file_get_contents('php://input'), true);
              $title = $_POST['title'];
              $completed = $_POST['completed'];
          }

        header('Content-Type: application/json');
        if (empty($title)) {
            echo json_encode(['status' => 'error', 'message' => 'Name must be filled'], JSON_PRETTY_PRINT);
        } elseif (!isset($completed)) {
            echo json_encode(['status' => 'error', 'message' => 'completed must be filled'], JSON_PRETTY_PRINT);
        } else {
            $createTask = $this->createTask($conn, $title, $completed);
            if ($createTask['status'] === 'success') {
                $conn->commit();
                echo json_encode(['status' => $createTask['status'], 'message' => $createTask['message'], 'data' =>$createTask['data']], JSON_PRETTY_PRINT);
            } else {
                $conn->rollback();
                echo json_encode(['status' => $createTask['status'], 'message' => $createTask['message']], JSON_PRETTY_PRINT);
            }
        }
    }
    private function createTask(mysqli $conn, string $title, int $completed): array
    {
        try {
            $dateTime = new DateTime();
            $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
            $id = substr(sha1(time()), 0, 10);

            $query = "INSERT INTO tasks (id, title, completed, created_at, updated_at) VALUES ('$id', '$title', '$completed', '$formattedDateTime', '$formattedDateTime')";
            $result = $conn->query($query);

            if ($result) {
                $conn->commit();
                $newTask = [
                    'id' => $id,
                    'title' => $title,
                    'completed' => $completed,
                    'created_at' => $formattedDateTime,
                    'updated_at' => $formattedDateTime
                ];
                return ['status' => 'success', 'message'=> 'success CreateTask', 'data' => $newTask];
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            return (['status' => 'error', 'message' => "Failed: " . $e->getMessage()]);
        }
    }
}
