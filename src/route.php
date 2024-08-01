<?php
function Route() {
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
  
  $uri = $_SERVER['REQUEST_URI'];
  $parsed_url = parse_url($uri);
  $path = $parsed_url['path'];
  $query = isset($parsed_url['query']) ? $parsed_url['query'] : '';

  switch (true) {
    case $uri === '/register':
        require 'controller/user_controller.php';
        $controller = new UserController();
        $controller->Register();
        break;

    case $uri === '/hello':
        require 'controller/user_controller.php';
        $controller = new UserController();
        $controller->hello();
        break;
        
    case preg_match('/^\/confirm\/?$/', $path):
        require 'controller/user_controller.php';
        parse_str($query, $query_params);
        if (isset($query_params['token'])) {
            $token = $query_params['token'];
            require 'controller/task_controller.php';
            $controller = new UserController();
            $controller->ConfirmEmail($token);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Token is missing'], JSON_PRETTY_PRINT);
        }
        break;

    case $uri === '/login':
        require 'controller/user_controller.php';
        $controller = new UserController();
        $controller->Login();
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
        case preg_match('/^\/confirm\/([a-zA-Z0-9]+)$/', $path, $matches):
        require 'controller/user_controller.php';
        $token = $matches[1];
        require 'controller/task_controller.php';
        $controller = new UserController();
        $controller->ConfirmEmail($token);
        break;
    
      default:
          header("HTTP/1.0 404 Not Found");
          echo json_encode(["status" => "error", "message" => "Endpoint not found"]);
          break;
  }
}
