<?php
require 'vendor/autoload.php';

function ConnectDB() : mysqli {
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
  $dbHost = $_ENV['MYSQL_HOST'];
  $dbUserName = $_ENV['MYSQL_USER'];
  $dbPassword = $_ENV['MYSQL_PASSWORD'];
  $dbName = $_ENV['MYSQL_DB'];
  $conn = new mysqli($dbHost, $dbUserName, $dbPassword, $dbName);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  return $conn;
}