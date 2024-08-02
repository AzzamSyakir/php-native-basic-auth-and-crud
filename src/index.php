<?php
require 'route.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ );
$dotenv->load();
Route();