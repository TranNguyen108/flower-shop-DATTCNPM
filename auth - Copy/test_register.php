<?php
// Test register functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

echo "<h2>Test Register</h2>";

// Test database connection
echo "<p>1. Database connection: ";
if ($conn) {
    echo "<span style='color:green'>OK</span></p>";
} else {
    echo "<span style='color:red'>FAILED - " . mysqli_connect_error() . "</span></p>";
    exit;
}

// Test hash_password function
echo "<p>2. hash_password function: ";
if (function_exists('hash_password')) {
    $test_hash = hash_password('test123');
    echo "<span style='color:green'>OK - Hash: " . substr($test_hash, 0, 20) . "...</span></p>";
} else {
    echo "<span style='color:red'>FUNCTION NOT FOUND</span></p>";
}

// Test db_insert function
echo "<p>3. db_insert function: ";
if (function_exists('db_insert')) {
    echo "<span style='color:green'>EXISTS</span></p>";
} else {
    echo "<span style='color:red'>FUNCTION NOT FOUND</span></p>";
}

// Test db_fetch_one function
echo "<p>4. db_fetch_one function: ";
if (function_exists('db_fetch_one')) {
    echo "<span style='color:green'>EXISTS</span></p>";
} else {
    echo "<span style='color:red'>FUNCTION NOT FOUND</span></p>";
}

// Test CSRF functions
echo "<p>5. CSRF functions: ";
if (function_exists('generate_csrf_token') && function_exists('csrf_field')) {
    echo "<span style='color:green'>OK</span></p>";
    echo "<p>CSRF Token: " . generate_csrf_token() . "</p>";
} else {
    echo "<span style='color:red'>FAILED</span></p>";
}

// Test insert new user
echo "<h3>Test Insert User</h3>";
$test_email = 'test_' . time() . '@test.com';
$test_name = 'Test User';
$test_pass = hash_password('test123');

echo "<p>Attempting to insert: $test_name / $test_email</p>";

try {
    $insert_id = db_insert(
        $conn, 
        "INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)",
        "ssss",
        [$test_name, $test_email, $test_pass, 'user']
    );
    
    if ($insert_id) {
        echo "<p style='color:green'>SUCCESS! Insert ID: $insert_id</p>";
        
        // Delete test user
        db_delete($conn, "DELETE FROM users WHERE id = ?", "i", [$insert_id]);
        echo "<p>Test user deleted.</p>";
    } else {
        echo "<p style='color:red'>FAILED - db_insert returned false</p>";
        echo "<p>MySQL Error: " . mysqli_error($conn) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
}

echo "<p><a href='register.php'>Go to Register Page</a></p>";
?>
