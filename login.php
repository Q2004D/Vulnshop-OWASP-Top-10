<?php
require_once 'config.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // VULNERABILITY: SQL Injection - String concatenation instead of prepared statements
    // VULNERABILITY: No logging of failed attempts
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    
    // VULNERABILITY: Information Disclosure - Show actual SQL query in development
    if (isset($_GET['debug'])) {
        echo "<pre>Debug Query: $query</pre>";
    }
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // VULNERABILITY: Session Fixation - No session regeneration
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // VULNERABILITY: No account lockout mechanism
        header('Location: index.php');
        exit;
    } else {
        // VULNERABILITY: Information Disclosure - Revealing whether user exists
        $error = "Invalid username or password. User may not exist.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>VulnShop - Login</title>
    <meta charset="utf-8">
    <!-- VULNERABILITY: Vulnerable & Outdated Components -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: #333; color: white; padding: 15px; margin-bottom: 20px; }
        .nav a { color: white; text-decoration: none; margin-right: 20px; }
        .nav a:hover { text-decoration: underline; }
        .login-form { background: white; padding: 30px; border-radius: 5px; border: 1px solid #ddd; max-width: 400px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .btn { background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer; width: 100%; }
        .btn:hover { background: #2980b9; }
        .error { color: #e74c3c; margin-bottom: 15px; padding: 10px; background: #fdf2f2; border: 1px solid #e74c3c; border-radius: 3px; }
        .debug { background: #f8f9fa; padding: 10px; margin: 10px 0; border: 1px solid #dee2e6; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VulnShop - Login</h1>
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

    <div class="login-form">
        <h2>Login to VulnShop</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- VULNERABILITY: No CSRF protection -->
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <!-- VULNERABILITY: Password visible in browser history if GET is used -->
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <p style="margin-top: 20px; text-align: center;">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
        
        <!-- VULNERABILITY: Information Disclosure - Debug functionality exposed -->
        <div class="debug">
            <strong>Debug Info:</strong><br>
            <a href="?debug=1">Show SQL Query (Debug Mode)</a><br>
            <small>Sample logins: admin/admin123, john/password123</small>
        </div>
    </div>

    <script>
        // VULNERABILITY: Client-side validation only
        $(document).ready(function() {
            $('form').submit(function() {
                var username = $('#username').val();
                var password = $('#password').val();
                
                // VULNERABILITY: Weak validation
                if (username.length < 1) {
                    alert('Username too short!');
                    return false;
                }
                
                if (password.length < 1) {
                    alert('Password too short!');
                    return false;
                }
            });
        });
    </script>
</body>
</html>