<?php
require_once 'config.php';

// VULNERABILITY: SQL Injection via GET parameter
$product_id = $_GET['id'];
$product_query = "SELECT * FROM products WHERE id = $product_id";
$product_result = $conn->query($product_query);
$product = $product_result->fetch_assoc();

if (!$product) {
    die("Product not found!");
}

// Handle comment submission
if ($_POST && isset($_POST['comment'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];
    
    // VULNERABILITY: SQL Injection and XSS - No sanitization
    $comment_query = "INSERT INTO comments (product_id, user_id, comment) VALUES ($product_id, $user_id, '$comment')";
    $conn->query($comment_query);
    
    // VULNERABILITY: No validation that comment was actually inserted
    header("Location: product.php?id=$product_id");
    exit;
}

// Get comments
$comments_query = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.product_id = $product_id ORDER BY c.created_at DESC";
$comments_result = $conn->query($comments_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>VulnShop - <?php echo $product['name']; ?></title>
    <meta charset="utf-8">
    <!-- VULNERABILITY: Vulnerable & Outdated Components -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: #333; color: white; padding: 15px; margin-bottom: 20px; }
        .nav a { color: white; text-decoration: none; margin-right: 20px; }
        .nav a:hover { text-decoration: underline; }
        .product-detail { background: white; padding: 30px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 20px; }
        .price { font-size: 24px; font-weight: bold; color: #e74c3c; margin: 15px 0; }
        .btn { background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin-right: 10px; }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .comments-section { background: white; padding: 30px; border-radius: 5px; border: 1px solid #ddd; }
        .comment { border-bottom: 1px solid #eee; padding: 15px 0; }
        .comment:last-child { border-bottom: none; }
        .comment-author { font-weight: bold; color: #333; }
        .comment-date { color: #666; font-size: 0.9em; }
        .comment-text { margin-top: 10px; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; resize: vertical; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VulnShop</h1>
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

    <div class="product-detail">
        <h1><?php 
            // VULNERABILITY: XSS - No output escaping
            echo $product['name']; 
        ?></h1>
        
        <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
        
        <p><strong>Description:</strong></p>
        <p><?php 
            // VULNERABILITY: XSS - No output escaping
            echo $product['description']; 
        ?></p>
        
        <p><strong>In Stock:</strong> <?php echo $product['stock']; ?> units</p>
        
        <?php if (isLoggedIn()): ?>
            <!-- VULNERABILITY: CSRF - No token protection -->
            <form method="POST" action="cart.php" style="display: inline;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                Quantity: <input type="number" name="quantity" value="1" min="-100" max="1000" style="width: 80px;">
                <button type="submit" name="add_to_cart" class="btn btn-success">Add to Cart</button>
            </form>
        <?php else: ?>
            <p><a href="login.php" class="btn">Login to Purchase</a></p>
        <?php endif; ?>
        
        <p><a href="index.php" class="btn">← Back to Products</a></p>
    </div>

    <div class="comments-section">
        <h3>Customer Reviews</h3>
        
        <?php if (isLoggedIn()): ?>
            <!-- VULNERABILITY: No CSRF protection -->
            <form method="POST" style="margin-bottom: 30px;">
                <h4>Leave a Review:</h4>
                <textarea name="comment" rows="4" placeholder="Write your review here..." required></textarea>
                <br><br>
                <button type="submit" class="btn">Submit Review</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Login</a> to leave a review.</p>
        <?php endif; ?>
        
        <h4>Reviews:</h4>
        
        <?php if ($comments_result && $comments_result->num_rows > 0): ?>
            <?php while ($comment = $comments_result->fetch_assoc()): ?>
                <div class="comment">
                    <div class="comment-author"><?php 
                        // VULNERABILITY: XSS - No output escaping
                        echo $comment['username']; 
                    ?></div>
                    <div class="comment-date"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></div>
                    <div class="comment-text"><?php 
                        // VULNERABILITY: XSS - No output escaping, allows script injection
                        echo $comment['comment']; 
                    ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>
    </div>

    <!-- VULNERABILITY: Information Disclosure - Debug information -->
    <?php if (isset($_GET['debug'])): ?>
        <div style="background: #f8f9fa; padding: 20px; margin-top: 20px; border: 1px solid #dee2e6; border-radius: 5px;">
            <h4>Debug Information:</h4>
            <pre>Product Query: <?php echo $product_query; ?></pre>
            <pre>Comments Query: <?php echo $comments_query; ?></pre>
            <pre>Product ID from URL: <?php echo $product_id; ?></pre>
            <pre>Session Data: <?php print_r($_SESSION); ?></pre>
        </div>
    <?php endif; ?>

    <script>
        // VULNERABILITY: Client-side logic that can be bypassed
        $(document).ready(function() {
            // Check if user is trying to add negative quantities
            $('input[name="quantity"]').on('change', function() {
                var qty = parseInt($(this).val());
                if (qty < 0) {
                    // VULNERABILITY: Warning only, doesn't prevent submission
                    console.log('Warning: Negative quantity detected!');
                }
                if (qty > 100) {
                    // VULNERABILITY: Client-side validation only
                    alert('Quantity seems high! Are you sure?');
                }
            });
        });
    </script>
</body>
</html>