<?php
/**
 * LOGIN API (PHP + MySQL + Redis)
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure error reporting doesn't break JSON output
error_reporting(0);
ini_set('display_errors', 0);

require_once '../db/mysql.php';
require_once '../db/redis.php'; // sets $redis = null if fails

// 📥 Read JSON input from frontend
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

// 🔒 Validate identifier (username/email) and password
$identifier = $data['identifier'] ?? '';
$password = $data['password'] ?? '';

if (empty($identifier) || empty($password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing credentials."]);
    exit();
}

// 👤 Query MySQL for user
try {
    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
        $stmt->close();
        exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // 🔑 Verify password
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
        exit();
    }

    // 🎟️ Generate token
    $token = bin2hex(random_bytes(32));

    // 🧊 Try Redis (but DON'T break if it fails)
    if ($redis !== null) {
        try {
            $redis->setex("session:" . $token, 3600, $user['id']);
        } catch (Throwable $e) {
            // Log error internally if needed, but continue execution
        }
    }

    // ✅ Success response
    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "token" => $token,
        "user_id" => $user['id']
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Internal server error: " . $e->getMessage()]);
}

if (isset($mysqli)) $mysqli->close();
?>
