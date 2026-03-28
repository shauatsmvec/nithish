<?php
/**
 * Redis Connection Script (Upstash)
 * Enhanced to handle TLS correctly and prevent application crashes.
 */

$redis_host = getenv("REDISHOST") !== false ? getenv("REDISHOST") : "powerful-anemone-38915.upstash.io";
$redis_port = getenv("REDISPORT") !== false ? getenv("REDISPORT") : 6379;
$redis_pass = getenv("REDISPASSWORD") !== false ? getenv("REDISPASSWORD") : "AZgDAAIncDExMjNiOGUxNWMwM2E0MDU5OWVlODBjNTEwMDhmNTk2Y3AxMzg5MTU";

$redis = null;

try {
    // Check if Redis extension is loaded
    if (class_exists('Redis')) {
        $redis = new Redis();
        
        // Upstash often requires 'tls://' prefix for external connections
        // Note: For some environments (like Railway) it might be just the host.
        // We use a try-catch to ensure we don't crash on failure.
        $connection_host = (strpos($redis_host, 'upstash.io') !== false) ? "tls://" . $redis_host : $redis_host;

        $connected = $redis->connect($connection_host, (int)$redis_port, 2.5); // 2.5s timeout
        
        if ($connected) {
            $redis->auth($redis_pass);
            
            // Verify connection
            $redis->ping();
        } else {
            $redis = null;
        }
    }
} catch (Throwable $e) {
    // Log error internally if needed, but DO NOT stop execution
    $redis = null;
}
?>
