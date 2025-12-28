<?php
/**
 * User Creation Script
 * Run this ONCE to create users with correct password hashes
 * Access: http://localhost/inventory-system/create_users.php
 */

require_once 'config/database.php';

echo "<h2>Creating Users...</h2>";

$conn = getDBConnection();

// Password to hash
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "<p><strong>Password:</strong> admin123</p>";
echo "<p><strong>Hashed:</strong> " . $hashedPassword . "</p><hr>";

// Delete existing users first (optional)
$conn->query("DELETE FROM users");
echo "<p>✓ Cleared existing users</p>";

// Insert users with properly hashed passwords
$users = [
    ['admin', 'admin@inventory.com', 'System Administrator', 'admin'],
    ['manager', 'manager@inventory.com', 'Stock Manager', 'manager'],
    ['staff', 'staff@inventory.com', 'Warehouse Staff', 'staff']
];

$stmt = $conn->prepare("
    INSERT INTO users (username, email, password, full_name, role) 
    VALUES (?, ?, ?, ?, ?)
");

foreach ($users as $user) {
    $stmt->bind_param("sssss", 
        $user[0],           // username
        $user[1],           // email
        $hashedPassword,    // password (hashed)
        $user[2],           // full_name
        $user[3]            // role
    );
    
    if ($stmt->execute()) {
        echo "<p>✓ Created user: <strong>{$user[0]}</strong> (Role: {$user[3]})</p>";
    } else {
        echo "<p>✗ Error creating user {$user[0]}: " . $stmt->error . "</p>";
    }
}

$stmt->close();
$conn->close();

echo "<hr>";
echo "<h3>Users Created Successfully! ✓</h3>";
echo "<p>All users have password: <strong>admin123</strong></p>";
echo "<ul>";
echo "<li>Username: <strong>admin</strong> / Password: admin123</li>";
echo "<li>Username: <strong>manager</strong> / Password: admin123</li>";
echo "<li>Username: <strong>staff</strong> / Password: admin123</li>";
echo "</ul>";
echo "<hr>";
echo "<p><a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
echo "<p style='color: red; margin-top: 20px;'><strong>IMPORTANT:</strong> Delete this file (create_users.php) after creating users for security!</p>";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Creation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        p { line-height: 1.6; }
        strong { color: #667eea; }
        hr { margin: 20px 0; border: none; border-top: 2px solid #ddd; }
    </style>
</head>
<body>
</body>
</html>