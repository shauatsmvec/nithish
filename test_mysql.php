<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once 'db/mysql.php';

echo json_encode(["status" => "success", "message" => "MySQL Connection Successful!", "host" => $mysqli->host_info]);
?>