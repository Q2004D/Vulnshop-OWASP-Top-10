<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'user';
    
    // VULNERABILITY: No input validation or sanitization
    // VULNERABILITY: Cryptographic Failures - Plain text password storage
    $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    
    // VULNERABILITY: SQL Injection via INSERT
    if ($conn->query($query)) {
        $success = "Account created successfully! You can now login.";
        // VULNERABILITY: No logging of account creation
    } else {
        // VULNERABILITY: Information Disclosure - Database error exposure
        $error = "Error creating account: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>VulnShop - Register</title>
    <meta charset="utf-8">
    <!-- VULNERABILITY: Vulnerable & Outdated Components -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: #333; color: white; padding: 15px; margin-bottom: 20px; }
        .nav a { color: white; text-decoration: none; margin-right: 20px; }
        .nav a:hover { text-decoration: underline; }
        .register-form { background: white; padding: 30px; border-radius: 5px; border: 1px solid #ddd; max-width: 400px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .btn { background: #27ae60; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer; width: 100%; }
        .btn:hover { background: #229954; }
        .error { color: #e74c3c; margin-bottom: 15px; padding: 10px; background: #fdf2f2; border: 1px solid #e74c3c; border-radius: 3px; }
        .success { color: #27ae60; margin-bottom: 15px; padding: 10px; background: #f2fdf4; border: 1px solid #27ae60; border-radius: 3px; }
        .danger-zone { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin-top: 20px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VulnShop - Register</h1>
        <div class="nav">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            <a href="cart.php">Cart</a>
            <a href="admin.php">Admin</a>
            <a href="upload.php">Upload</a>
            <a href="phpinfo.php">System Info</a>
        </div>
    </div>

    <div class="register-form">
        <h2>Create Account</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- VULNERABILITY: No CSRF protection -->
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <!-- VULNERABILITY: No length limits or character restrictions -->
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <!-- VULNERABILITY: No password strength requirements -->
                <input type="password" id="password" name="password" required>
                <div style="color:orange; margin-top:8px;">
                    <strong>⚠️ Warning: Passwords are stored in plain text with no complexity requirements!</strong>
                </div>
            </div>
            
            <!-- VULNERABILITY: Broken Access Control - User can set their own role -->
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role">
                    <option value="user">Regular User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Create Account</button>
        </form>
        
        <p style="margin-top: 20px; text-align: center;">
            Already have an account? <a href="login.php">Login here</a>
        </p>
        
        <div class="danger-zone">
            <strong>⚠️ Security Notice:</strong><br>
            <small>This application stores passwords in plain text and has no security measures. 
            For demonstration purposes only!</small>
        </div>
    </div>

    <script>
        // VULNERABILITY: Client-side only validation
        $(document).ready(function() {
            $('form').submit(function() {
                var username = $('#username').val();
                var password = $('#password').val();
                
                // VULNERABILITY: Extremely weak validation
                if (username.length < 1) {
                    alert('Please enter a username');
                    return false;
                }
                
                // VULNERABILITY: No password complexity requirements
                if (password.length < 1) {
                    alert('Please enter a password');
                    return false;
                }
                
                // VULNERABILITY: Warning user they can become admin
                if ($('#role').val() === 'admin') {
                    return confirm('You are registering as an admin. Continue?');
                }
                
                return true;
            });
        });
    </script>
</body>
</html>