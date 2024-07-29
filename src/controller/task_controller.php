<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../model/tasks.php';

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
    public function PatchOneById(string $id)
    {
        $conn = ConnectDB();
        
        if ($conn->connect_error) {
            echo json_encode(['status' => 'error', 'message' => "Connection failed: " . $conn->connect_error], JSON_PRETTY_PRINT);
            return;
        }
    
        $conn->begin_transaction();
        try {
            $query = "SELECT * FROM tasks WHERE id=?";
            $stmt = $conn->prepare($query);
    
            if ($stmt === false) {
                throw new Exception($conn->error);
            }
    
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $task = null;
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $task = new Task($row['id'], $row['title'], $row['completed'], $row['createdAt'], $row['updatedAt']);
            } else {
                throw new Exception("No task found with the given ID.");
            }
    
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => "GetTaskFailed: " . $e->getMessage()], JSON_PRETTY_PRINT);
            return;
        }
    
        if (!empty($task)) {
            try {
                $input = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);
                $title = isset($input['title']) ? $input['title'] : $task->title;
                $completed = isset($input['completed']) ? $input['completed'] : $task->completed;
                $dateTime = new DateTime();
                $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
                $task->title = $title;
                $task->completed = $completed;
                $task->updatedAt = $formattedDateTime;
                $stmt = $conn->prepare("UPDATE tasks SET title=?, completed=?, updated_at=? WHERE id=?");

                if ($stmt === false) {
                    $error_message = "Failed to prepare update statement: " . mysqli_error($conn);
                    error_log($error_message);
                    throw new Exception($error_message);
                }

                $stmt->bind_param('ssss', $task->title, $task->completed, $task->updatedAt, $id);
                $status = $stmt->execute();
                if ($status != false) {
                    $conn->commit();
                    echo json_encode(['status' => 'success', 'message' => 'Update Task Success', 'data' => $task], JSON_PRETTY_PRINT);
                } else {
                    throw new Exception("No rows affected. Update might have failed.");
                }
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => "UpdateTaskFailed: " . $e->getMessage()], JSON_PRETTY_PRINT);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No task found.'], JSON_PRETTY_PRINT);
        }
    }
    
}