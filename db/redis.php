<?php
/**
 * Redis Connection Script (Upstash)
 * Uses environment variables with hardcoded fallbacks for Render compatibility.
 */

$redis_host = getenv("REDISHOST") ?: "powerful-anemone-38915.upstash.io";
$redis_port = getenv("REDISPORT") ?: 6379;
$redis_pass = getenv("REDISPASSWORD") ?: "AZgDAAIncDExMjNiOGUxNWMwM2E0MDU5OWVlODBjNTEwMDhmNTk2Y3AxMzg5MTU";

$redis = null;

try {
    if (class_exists('Redis')) {
        $redis = new Redis();
        
        // Upstash often requires 'tls://' prefix for external connections
        $connection_host = (strpos($redis_host, 'upstash.io') !== false) ? "tls://" . $redis_host : $redis_host;

        $connected = @$redis->connect($connection_host, (int)$redis_port, 2.0); // 2s timeout
        
        if ($connected) {
            $redis->auth($redis_pass);
            $redis->ping();
        } else {
            $redis = null;
        }
    }
} catch (Throwable $e) {
    $redis = null;
}
?>
