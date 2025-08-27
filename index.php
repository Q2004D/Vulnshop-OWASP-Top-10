<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>VulnShop - Home</title>
    <meta charset="utf-8">
    <!-- VULNERABILITY: Vulnerable & Outdated Components - Old jQuery version -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: #333; color: white; padding: 15px; margin-bottom: 20px; }
        .nav a { color: white; text-decoration: none; margin-right: 20px; }
        .nav a:hover { text-decoration: underline; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .product-card { background: white; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .product-card h3 { margin-top: 0; color: #333; }
        .price { font-size: 18px; font-weight: bold; color: #e74c3c; }
        .btn { background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #2980b9; }
        .welcome { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VulnShop - Your Insecure Shopping Destination</h1>
        <div class="nav">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            <a href="cart.php">Cart</a>
            <a href="admin.php">Admin</a>
            <a href="upload.php">Upload</a>
            <a href="phpinfo.php">System Info</a>
            <?php if (isLoggedIn()): ?>
                <a href="?logout=1">Logout</a>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // Handle logout
    if (isset($_GET['logout'])) {
        // VULNERABILITY: No proper session cleanup
        session_destroy();
        header('Location: index.php');
        exit;
    }
    ?>

    <div class="welcome">
        <?php if (isLoggedIn()): 
            $user = getCurrentUser();
            // VULNERABILITY: XSS - No output escaping
            echo "<p>Welcome back, " . $user['username'] . "!</p>";
        else: ?>
            <p>Welcome to VulnShop! Please <a href="login.php">login</a> or <a href="register.php">register</a> to start shopping.</p>
        <?php endif; ?>
    </div>

    <h2>Our Products</h2>
    
    <div class="product-grid">
        <?php
        // Get all products
        $result = $conn->query("SELECT * FROM products ORDER BY name");
        
        while ($product = $result->fetch_assoc()):
        ?>
        <div class="product-card">
            <h3><?php 
                // VULNERABILITY: XSS - No output escaping
                echo $product['name']; 
            ?></h3>
            <p><?php 
                // VULNERABILITY: XSS - No output escaping
                echo $product['description']; 
            ?></p>
            <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
            <p>In Stock: <?php echo $product['stock']; ?></p>
            
            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
            
            <?php if (isLoggedIn()): ?>
                <!-- VULNERABILITY: CSRF - No token protection -->
                <form method="POST" action="cart.php" style="display: inline;">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" max="100" style="width: 60px;">
                    <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>

    <div style="margin-top: 40px; padding: 20px; background: #fff; border-radius: 5px; border: 1px solid #ddd;">
        <h3>Search Products</h3>
        <!-- VULNERABILITY: XSS via search parameter -->
        <form method="GET">
            <input type="text" name="search" placeholder="Search products..." 
                   value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" 
                   style="padding: 8px; width: 300px;">
            <button type="submit" class="btn">Search</button>
        </form>
        
        <?php if (isset($_GET['search'])): ?>
            <p>Search results for: <strong><?php 
                // VULNERABILITY: XSS - No output escaping
                echo $_GET['search']; 
            ?></strong></p>
        <?php endif; ?>
    </div>
</body>
</html>