<?php
function Route() {
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $method = $_SERVER['REQUEST_METHOD'];
  
  switch ($uri) {
    
  case '/api/users':
    require 'controller/user_controller.php';
    $controller = new UserController();
    if ($method == 'GET') {
    $controller->get();
    } elseif ($method == 'POST') {
    $controller->Post();
    }
    break;
  case '/api/tasks':
        require 'controller/task_controller.php';
        $controller = new TaskController();
        if ($method == 'GET') {
        $controller->FetchTask();
        } elseif ($method == 'POST') {
        $controller->CreateTask();
        }
        break;
  case '/api/tasks/:id':
          require 'controller/task_controller.php';
          $controller = new TaskController();
          if ($method == 'GET') {
          $controller->Get();
          } elseif ($method == 'POST') {
          $controller->CreateTask();
          }
          break;

  default:
          header("HTTP/1.0 404 Not Found");
          echo "404 Not Found";
          break;
  
}
}