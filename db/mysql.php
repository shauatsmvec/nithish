<?php
/**
 * MySQL Connection Script (Railway)
 * Uses environment variables with hardcoded fallbacks for Render compatibility.
 */

$mysql_host = getenv("MYSQLHOST") ?: "gondola.proxy.rlwy.net";
$mysql_port = getenv("MYSQLPORT") ?: 45986;
$mysql_user = getenv("MYSQLUSER") ?: "root";
$mysql_pass = getenv("MYSQLPASSWORD") ?: "DEXLvQAIknfwXuVjiXCArfrWKWSbWNtv";
$mysql_db   = getenv("MYSQLDATABASE") ?: "railway";

mysqli_report(MYSQLI_REPORT_OFF);

try {
    $mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db, (int)$mysql_port);
    
    if ($mysqli->connect_errno) {
        throw new Exception($mysqli->connect_error);
    }
} catch (Throwable $e) {
    if (!headers_sent()) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(500);
    }
    die(json_encode([
        "status" => "error", 
        "message" => "Database link failure: " . $e->getMessage()
    ]));
}
?>
