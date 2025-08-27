<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// VULNERABILITY: Broken Access Control - Any logged-in user can access
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // VULNERABILITY: No validation on file type or extension
    // VULNERABILITY: No sanitization of file name
    $filename = $_FILES['file']['name'];
    $filepath = $upload_dir . $filename;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
        $message = "<div style='color:green'>File uploaded! <a href='uploads/$filename' target='_blank'>View file</a></div>";
    } else {
        $message = "<div style='color:red'>Upload failed!</div>";
    }
}

// List all uploaded files
$files = array_diff(scandir($upload_dir), array('.', '..'));

?>
<!DOCTYPE html>
<html>
<head>
    <title>VulnShop - File Upload</title>
    <script src="https://code.jquery.com/jquery-1.7.2.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>File Upload</h2>
        <!-- VULNERABILITY: No file type validation -->
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit">Upload</button>
        </form>
        <?= $message ?>
        <h3>Uploaded Files</h3>
        <ul>
            <?php foreach ($files as $f): ?>
                <li><a href="uploads/<?= htmlspecialchars($f) ?>" target="_blank"><?= htmlspecialchars($f) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <!-- VULNERABILITY: Direct links to uploaded files -->
</body>
</html><?php
require_once 'config.php';

// VULNERABILITY: Broken Access Control - Any logged in user can access admin panel
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';

// Handle SSRF functionality
if (isset($_POST['fetch_url'])) {
    $url = $_POST['url'];
    
    // VULNERABILITY: SSRF - No URL validation, allows internal network access
    $content = @file_get_contents($url);
    
    if ($content !== false) {
        $message = "URL fetched successfully!";
        $fetched_content = $content;
    } else {
        $message = "Failed to fetch URL.";
    }
}

// Handle user management
if (isset($_POST['add_user'])) {
    $username = $_POST['new_username'];
    $password = $_POST['new_password'];
    $role = $_POST['new_role'];
    
    // VULNERABILITY: SQL Injection via admin form
    $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    
    if ($conn->query($query)) {
        $message = "User added successfully!";
    } else {
        $message = "Error adding user: " . $conn->error;
    }
}

// Handle product management
if (isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $description = $_POST['product_description'];
    $price = $_POST['product_price'];
    $stock = $_POST['product_stock'];
    
    // VULNERABILITY: SQL Injection via admin form
    $query = "INSERT INTO products (name, description, price, stock) VALUES ('$name', '$description', $price, $stock)";
    
    if ($conn->query($query)) {
        $message = "Product added successfully!";
    } else {
        $message = "Error adding product: " . $conn->error;
    }
}

// Get current user info
$user = getCurrentUser();

// Get all users and products for management
$users_result = $conn->query("SELECT * FROM users ORDER BY id");
$products_result = $conn->query("SELECT * FROM products ORDER BY id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>VulnShop - Admin Dashboard</title>
    <meta charset="utf-8">
    <!-- VULNERABILITY: Vulnerable & Outdated Components -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: #333; color: white; padding: 15px; margin-bottom: 20px; }
        .nav a { color: white; text-decoration: none; margin-right: 20px; }
        .nav a:hover { text-decoration: underline; }
        .admin-container { background: white; padding: 30px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 20px; }
        .admin-section { margin-bottom: 40px; }
        .admin-section h3 { border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        .form-group textarea { height: 100px; resize: vertical; }
        .btn { background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin-right: 10px; }
        .btn:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .data-table th { background: #f8f9fa; font-weight: bold; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .fetched-content { background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-radius: 5px; max-height: 300px; overflow-y: auto; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VulnShop - Admin Dashboard</h1>
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

    <div class="admin-container">
        <h2>Admin Dashboard</h2>
        
        <!-- VULNERABILITY: Broken Access Control - Show user's actual role -->
        <div class="warning">
            <strong>⚠️ Access Control Issue:</strong> You are logged in as: <strong><?php echo $user['username']; ?></strong> 
            with role: <strong><?php echo $user['role']; ?></strong>
            <?php if ($user['role'] !== 'admin'): ?>
                <br>Note: You are not an admin, but you can still access this page!
            <?php endif; ?>
        </div>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- SSRF Vulnerability Section -->
        <div class="admin-section">
            <h3>🌐 URL Fetcher (SSRF Demo)</h3>
            <p>Fetch content from any URL:</p>
            <!-- VULNERABILITY: No CSRF protection -->
            <form method="POST">
                <div class="form-group">
                    <label for="url">URL to fetch:</label>
                    <input type="url" id="url" name="url" placeholder="http://example.com" 
                           value="<?php echo isset($_POST['url']) ? $_POST['url'] : ''; ?>">
                </div>
                <button type="submit" name="fetch_url" class="btn">Fetch URL</button>
            </form>
            
            <p><small>Try URLs like: http://localhost/admin.php, http://127.0.0.1:80, file:///etc/passwd</small></p>
            
            <?php if (isset($fetched_content)): ?>
                <h4>Fetched Content:</h4>
                <div class="fetched-content"><?php 
                    // VULNERABILITY: XSS - No output escaping of fetched content
                    echo $fetched_content; 
                ?></div>
            <?php endif; ?>
        </div>

        <!-- User Management Section -->
        <div class="admin-section">
            <h3>👥 User Management</h3>
            
            <!-- VULNERABILITY: No CSRF protection -->
            <form method="POST">
                <div class="form-group">
                    <label for="new_username">Username:</label>
                    <input type="text" id="new_username" name="new_username" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="new_role">Role:</label>
                    <select id="new_role" name="new_role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn btn-success">Add User</button>
            </form>
            
            <h4>Existing Users:</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user_row = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user_row['id']; ?></td>
                            <td><?php 
                                // VULNERABILITY: XSS - No output escaping
                                echo $user_row['username']; 
                            ?></td>
                            <td><?php 
                                // VULNERABILITY: Cryptographic Failures - Displaying plain text passwords
                                echo $user_row['password']; 
                            ?></td>
                            <td><?php echo $user_row['role']; ?></td>
                            <td><?php echo $user_row['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Product Management Section -->
        <div class="admin-section">
            <h3>📦 Product Management</h3>
            
            <!-- VULNERABILITY: No CSRF protection -->
            <form method="POST">
                <div class="form-group">
                    <label for="product_name">Product Name:</label>
                    <input type="text" id="product_name" name="product_name" required>
                </div>
                <div class="form-group">
                    <label for="product_description">Description:</label>
                    <textarea id="product_description" name="product_description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="product_price">Price:</label>
                    <input type="number" id="product_price" name="product_price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="product_stock">Stock:</label>
                    <input type="number" id="product_stock" name="product_stock" min="0" required>
                </div>
                <button type="submit" name="add_product" class="btn btn-success">Add Product</button>
            </form>
            
            <h4>Existing Products:</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product_row = $products_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product_row['id']; ?></td>
                            <td><?php 
                                // VULNERABILITY: XSS - No output escaping
                                echo $product_row['name']; 
                            ?></td>
                            <td><?php 
                                // VULNERABILITY: XSS - No output escaping
                                echo substr($product_row['description'], 0, 100); 
                            ?>...</td>
                            <td>$<?php echo number_format($product_row['price'], 2); ?></td>
                            <td><?php echo $product_row['stock']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- System Information -->
        <div class="admin-section">
            <h3>🖥️ System Information</h3>
            <p><a href="phpinfo.php" class="btn" target="_blank">View PHP Info</a></p>
            <p><a href="upload.php" class="btn">File Upload</a></p>
            
            <!-- VULNERABILITY: Information Disclosure -->
            <h4>Server Details:</h4>
            <table class="data-table">
                <tr><td>Server Software</td><td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td></tr>
                <tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
                <tr><td>Document Root</td><td><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td></tr>
                <tr><td>Server Name</td><td><?php echo $_SERVER['SERVER_NAME']; ?></td></tr>
            </table>
        </div>
    </div>

    <script>
        // VULNERABILITY: Client-side admin check that can be bypassed
        $(document).ready(function() {
            // Check if user should have admin access
            var userRole = '<?php echo $user['role']; ?>';
            
            if (userRole !== 'admin') {
                console.log('Warning: Non-admin user accessing admin panel!');
                // VULNERABILITY: Warning only, doesn't actually restrict access
            }
            
            // SSRF URL suggestions
            $('#url').on('focus', function() {
                if ($(this).val() === '') {
                    $(this).attr('placeholder', 'Try: http://localhost, http://127.0.0.1:22, file:///etc/passwd');
                }
            });
        });
    </script>
</body>
</html>