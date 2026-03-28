<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once 'db/redis.php';

try {
    $ping = $redis->ping();
    echo json_encode(["status" => "success", "message" => "Redis Connection Successful!", "ping_response" => $ping]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>