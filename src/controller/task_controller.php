<?php
require_once __DIR__ . '/../db.php';

class TaskController
{
    public function FetchTask()
    {
        $conn = ConnectDB();
        $task = [];
        try {
            $query = "SELECT * FROM tasks";
            $result = $conn->query($query);

            if ($result) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $task = $row;
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

        
        header('Content-Type: application/json');
        if (!empty($task)) {
            echo json_encode(['status' => 'success', 'data' => $task], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No task found.'], JSON_PRETTY_PRINT);
        }
    }
    public function CreateTask()
    {
        $conn = ConnectDB();
        $input = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);
        $title = isset($input['title']) ? $input['title'] : null;

        header('Content-Type: application/json');
        if (empty($title)) {
            echo json_encode(['status' => 'error', 'message' => 'Name must be filled'], JSON_PRETTY_PRINT);
        } else {
            try {
                $dateTime = new DateTime();
                $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
                $id = substr(sha1(time()), 0, 10);
    
                $query = "INSERT INTO tasks (id, title, completed, created_at, updated_at) VALUES ('$id', '$title', 0, '$formattedDateTime', '$formattedDateTime')";
                $result = $conn->query($query);
    
                if ($result) {
                    $conn->commit();
                    $newTask = [
                        'id' => $id,
                        'title' => $title,
                        'completed' => 0,
                        'created_at' => $formattedDateTime,
                        'updated_at' => $formattedDateTime
                    ];
                } else {
                    throw new Exception($conn->error);
                }
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'CreateTask Success', 'data' =>$newTask], JSON_PRETTY_PRINT);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Failed: '. $e->getMessage()], JSON_PRETTY_PRINT);
            }
        }
    }
    public function GetOneById(string $id)
    {
        $conn = ConnectDB();
        $conn->begin_transaction();
        $task = null;
        try {
            $query = "SELECT * FROM tasks WHERE id=?";
            $stmt = $conn->prepare($query);

            if ($stmt === false) {
                throw new Exception($conn->error);
            }
    
            $stmt->bind_param('s', $id);
    
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $task = $row;
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
        if (!empty($task)) {
            echo json_encode(['status' => 'success', 'data' => $task], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No task found.'], JSON_PRETTY_PRINT);
        }   
    }
}