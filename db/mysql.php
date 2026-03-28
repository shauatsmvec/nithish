<?php
/**
 * MySQL Connection Script (Railway)
 */

$mysql_host = getenv("MYSQLHOST") !== false ? getenv("MYSQLHOST") : "gondola.proxy.rlwy.net";
$mysql_port = getenv("MYSQLPORT") !== false ? getenv("MYSQLPORT") : 45986;
$mysql_user = getenv("MYSQLUSER") !== false ? getenv("MYSQLUSER") : "root";
$mysql_pass = getenv("MYSQLPASSWORD") !== false ? getenv("MYSQLPASSWORD") : "DEXLvQAIknfwXuVjiXCArfrWKWSbWNtv";
$mysql_db   = getenv("MYSQLDATABASE") !== false ? getenv("MYSQLDATABASE") : "railway";

// Ensure no warnings break JSON
mysqli_report(MYSQLI_REPORT_OFF);

try {
    $mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db, (int)$mysql_port);
    
    if ($mysqli->connect_errno) {
        throw new Exception($mysqli->connect_error);
    }
} catch (Throwable $e) {
    // Clean JSON output for connection errors
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
