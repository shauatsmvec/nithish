<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once 'db/mongo.php';

try {
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $cursor = $mongo_manager->executeCommand('admin', $command);
    $response = $cursor->toArray()[0];
    echo json_encode(["status" => "success", "message" => "MongoDB Connection Successful!", "ping_response" => $response]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>