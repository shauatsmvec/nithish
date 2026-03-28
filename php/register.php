<?php
/**
 * REGISTER API (PHP + MySQL + MongoDB)
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
require_once '../db/mongo.php'; // sets $mongo_manager = null if fails

// 📥 Read JSON input from frontend
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON input."]);
    exit();
}

// 🔒 Extract and validate fields
$email = trim($data['email'] ?? '');
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');
$name = trim($data['name'] ?? '');
$age = (int)($data['age'] ?? 0);
$dob = trim($data['dob'] ?? '');
$mobile = trim($data['mobile'] ?? '');

if (empty($email) || empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields (email, username, password)."]);
    exit();
}

try {
    // 🔍 Check if user already exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Email or Username already exists."]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // 👤 Insert into MySQL
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $insert_stmt = $mysqli->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $email, $username, $hashed_password);

    if (!$insert_stmt->execute()) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error during registration."]);
        $insert_stmt->close();
        exit();
    }

    $user_id = $insert_stmt->insert_id;
    $insert_stmt->close();

    // 📦 Insert profile into MongoDB Atlas
    if ($mongo_manager !== null) {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $profile_document = [
                "user_id" => (int)$user_id,
                "name" => $name,
                "email" => $email,
                "age" => $age,
                "dob" => $dob,
                "mobile" => $mobile,
                "created_at" => new MongoDB\BSON\UTCDateTime(),
                "profile_pic" => "https://via.placeholder.com/150"
            ];
            $bulk->insert($profile_document);
            $mongo_manager->executeBulkWrite("user_system.profiles", $bulk);
        } catch (Throwable $mongo_e) {
            // Log MongoDB error but we already registered in MySQL
            // In a production app, we might want to use a transaction or rollback
        }
    }

    // ✅ Success response
    echo json_encode([
        "status" => "success",
        "message" => "User registered successfully!",
        "user_id" => $user_id
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Internal server error: " . $e->getMessage()]);
}

if (isset($mysqli)) $mysqli->close();
?>
