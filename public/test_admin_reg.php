<?php
echo "<h2>Admin Registration Test</h2>";
echo "<p>Testing admin registration system fixes...</p>";

echo "<h3>Environment Check:</h3>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Current Directory: " . getcwd() . "</li>";
echo "<li>Laravel Project Root: " . realpath('../') . "</li>";
echo "</ul>";

// Try to load Laravel
try {
    require_once '../vendor/autoload.php';
    echo "<p>✓ Composer autoload successful</p>";
    
    $app = require_once '../bootstrap/app.php';
    echo "<p>✓ Laravel bootstrap successful</p>";
    
    // Test database
    $db = $app->make('db');
    $adminCount = $db->table('admin_users')->count();
    echo "<p>✓ Database connection successful. Admin count: {$adminCount}</p>";
    
    echo "<h3>Registration Form Test:</h3>";
    echo '<form action="../admin/register" method="POST">';
    echo '<input type="hidden" name="_token" value="test-token">';
    echo '<p><input type="text" name="name" placeholder="Full Name" required></p>';
    echo '<p><input type="email" name="email" placeholder="Email" required></p>';
    echo '<p><input type="tel" name="phone" placeholder="Phone (optional)"></p>';
    echo '<p><input type="password" name="password" placeholder="Password" required></p>';
    echo '<p><input type="password" name="password_confirmation" placeholder="Confirm Password" required></p>';
    echo '<p><button type="submit">Test Registration</button></p>';
    echo '</form>';
    
    echo "<p><strong>Form URL:</strong> <a href='../admin/register'>../admin/register</a></p>";
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>