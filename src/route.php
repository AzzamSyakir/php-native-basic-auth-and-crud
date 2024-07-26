<?php
function Route() {
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
  
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $method = $_SERVER['REQUEST_METHOD'];

  switch (true) {
      case $uri === '/api/users':
          require 'controller/user_controller.php';
          $controller = new UserController();
          if ($method == 'GET') {
              $controller->get();
          } elseif ($method == 'POST') {
              $controller->Post();
          }
          break;

      case $uri === '/api/tasks':
          require 'controller/task_controller.php';
          $controller = new TaskController();
          if ($method == 'GET') {
              $controller->FetchTask();
          } elseif ($method == 'POST') {
              $controller->CreateTask();
          }
          break;

      case preg_match('/^\/api\/tasks\/([a-zA-Z0-9]+)$/', $uri, $matches);
        require 'controller/task_controller.php';
        $controller = new TaskController();
        $id = $matches[1]; // Menangkap ID dari URI
        
        if ($method == 'GET') {
            $controller->GetOneById($id);
        } elseif ($method == 'PATCH') {
            $controller->Patch($id);
        }
        break;

      default:
          header("HTTP/1.0 404 Not Found");
          echo json_encode(["status" => "error", "message" => "Endpoint not found"]);
          break;
  }
}
