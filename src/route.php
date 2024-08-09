<?php
require 'db.php';
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
  $conn = ConnectDB();
  switch (true) {
    case $uri === '/register':
        require 'controller/user_controller.php';
        $controller = new UserController();
        $controller->Register($conn);
        break;

    case $uri === '/hello':
        require 'middleware.php';
        require 'controller/user_controller.php';
        $middleware = new Middleware();
        if ($middleware->ValidateAccToken($conn)) {
            $controller = new UserController();
            $controller->hello();
        }
        break;
    case $uri === '/home':
        require 'controller/home_controller.php';
        break;
    case $uri === '/logout':
        require 'middleware.php';
        require 'controller/user_controller.php';
        $middleware = new Middleware();
        if ($middleware->ValidateAccToken($conn)) {
            $controller = new UserController();
            $controller->Logout($conn);
        }
        break;
    case $uri === '/token':
        require 'middleware.php';
        require 'controller/user_controller.php';
        $middleware = new Middleware();
        if ($middleware->ValidateRefToken($conn)) {
            $controller = new UserController();
            $controller->GenerateAccToken($conn);
        }
        break;
        
    case preg_match('/^\/confirm\/?$/', $path):
        require 'controller/user_controller.php';
        parse_str($query, $query_params);
        if (isset($query_params['token'])) {
            $token = $query_params['token'];
            require 'controller/task_controller.php';
            $controller = new UserController();
            $controller->ConfirmEmail($conn, $token);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Token is missing'], JSON_PRETTY_PRINT);
        }
        break;

    case preg_match('/^\/confirm\/([a-zA-Z0-9]+)$/', $path, $matches):
        require 'controller/user_controller.php';
        $token = $matches[1];
        require 'controller/task_controller.php';
        $controller = new UserController();
        $controller->ConfirmEmail($conn, $token);
        break;


    case $uri === '/login':
        require 'controller/user_controller.php';
        $controller = new UserController();
        $controller->Login($conn);
        break;



    case $uri === '/forgot-password':
        require 'controller/user_controller.php';
        $controller = new UserController();
        $controller->ForgotPassword($conn);
        break;

    case preg_match('/^\/reset-password\/?$/', $path):
        require 'controller/user_controller.php';
        parse_str($query, $query_params);
        if (isset($query_params['id'])) {
            $id = $query_params['id'];
            require 'controller/task_controller.php';
            $controller = new UserController();
            $controller->ResetPassword($conn, $id);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Token is missing'], JSON_PRETTY_PRINT);
        }
        break;

    case preg_match('/^\/reset-password\/([a-zA-Z0-9]+)$/', $path, $matches):
        require 'controller/user_controller.php';
        $id = $matches[1];
        require 'controller/task_controller.php';
        $controller = new UserController();
        $controller->ResetPassword($conn, $id);
        break;
    


    case $uri === '/api/tasks':
        require 'controller/task_controller.php';
        $controller = new TaskController();
        if ($method == 'GET') {
            $controller->FetchTask($conn);
        } elseif ($method == 'POST') {
            $controller->CreateTask($conn);
        }
        break;
     
    
      default:
          header("HTTP/1.0 404 Not Found");
          echo json_encode(["status" => "error", "message" => "Endpoint not found"]);
          break;
  }
}
