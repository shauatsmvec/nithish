<?php
/**
 * PROFILE API (PHP + MySQL + MongoDB)
 * Enhanced to handle full profile data including DOB.
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-ID");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(0);
ini_set('display_errors', 0);

require_once '../db/mysql.php';
require_once '../db/mongo.php';
require_once '../db/redis.php';

function getHeader($name) {
    $name = strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER['HTTP_' . $name])) {
        return $_SERVER['HTTP_' . $name];
    }
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $key => $value) {
            if (strcasecmp($key, $name) == 0 || strcasecmp(str_replace('-', '_', $key), $name) == 0) {
                return $value;
            }
        }
    }
    return null;
}

$authHeader = getHeader('Authorization') ?: '';
$token = '';
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
}

$user_id = null;
if ($redis !== null && !empty($token)) {
    try {
        $user_id = $redis->get("session:" . $token);
    } catch (Throwable $e) { $user_id = null; }
}

if (!$user_id) {
    $user_id = getHeader('X-User-ID');
}

if (!$user_id) {
    http_response_code(401);
    die(json_encode(["status" => "error", "message" => "Unauthorized."]));
}

$user_id = (int)$user_id;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $mysqli->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $mysql_user_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$mysql_user_data) {
            http_response_code(404);
            die(json_encode(["status" => "error", "message" => "User not found."]));
        }

        $profile_data = null;
        if ($mongo_manager !== null) {
            try {
                $filter = ['user_id' => $user_id];
                $query = new MongoDB\Driver\Query($filter);
                $cursor = $mongo_manager->executeQuery("user_system.profiles", $query);
                $results = $cursor->toArray();
                if (!empty($results)) {
                    $profile_data = (array)$results[0];
                }
            } catch (Throwable $e) {}
        }

        echo json_encode([
            "status" => "success",
            "user" => $mysql_user_data,
            "profile" => $profile_data ?: [
                "user_id" => $user_id,
                "name" => "User " . $user_id,
                "age" => "N/A",
                "dob" => "N/A",
                "mobile" => "N/A"
            ]
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        die(json_encode(["status" => "error", "message" => "Invalid payload."]));
    }

    $name = $data['name'] ?? null;
    $age = $data['age'] ?? null;
    $dob = $data['dob'] ?? null;
    $mobile = $data['mobile'] ?? null;
    $password = $data['password'] ?? null;

    try {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        if ($mongo_manager !== null) {
            $bulk = new MongoDB\Driver\BulkWrite;
            $update_fields = [];
            if ($name !== null) $update_fields['name'] = $name;
            if ($age !== null) $update_fields['age'] = (int)$age;
            if ($dob !== null) $update_fields['dob'] = $dob;
            if ($mobile !== null) $update_fields['mobile'] = $mobile;

            if (!empty($update_fields)) {
                $bulk->update(
                    ['user_id' => $user_id],
                    ['$set' => $update_fields],
                    ['multi' => false, 'upsert' => true]
                );
                $mongo_manager->executeBulkWrite("user_system.profiles", $bulk);
            }
        }
        
        echo json_encode(["status" => "success", "message" => "Vault updated."]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if (isset($mysqli)) $mysqli->close();
?>
