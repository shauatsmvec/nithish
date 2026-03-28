<?php
// MongoDB Atlas connection string
// Ensure you have the 'mongodb' extension enabled in php.ini

$mongo_uri = "mongodb+srv://iglcyborg143_db_user:dU_8rxC4PQHxWdT@cluster0.nay1qeh.mongodb.net/user_system";

try {
    // Create the MongoDB Manager
    $mongo_manager = new MongoDB\Driver\Manager($mongo_uri);
} catch (Throwable $e) {
    // If connection fails, set manager to null to prevent backend crash
    $mongo_manager = null;
}
?>
