<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Basic validation to prevent SQL errors (while keeping vulnerability for training)
    if (!empty($product_id) && is_numeric($product_id) && !empty($quantity) && is_numeric($quantity)) {
        // VULNERABILITY: Insecure Design - No stock validation
        // VULNERABILITY: Insecure Design - Negative quantities allowed
        // VULNERABILITY: No input validation on quantity
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        $message = "Added to cart successfully!";
    } else {
        $message = "Invalid product or quantity!";
    }
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    // Basic validation to prevent issues
    if (is_numeric($product_id) && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $message = "Item removed from cart!";
    }
}

// Handle update quantity
if (isset($_POST['update_cart'])) {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            // Basic validation to prevent errors
            if (is_numeric($product_id) && is_numeric($quantity)) {
                // VULNERABILITY: Insecure Design - Negative quantities and no validation
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
            }
        }
        $message = "Cart updated!";
    }
}

// Handle checkout
if (isset($_POST['checkout'])) {
    // VULNERABILITY: Insecure Design - No payment processing, no stock reduction
    // VULNERABILITY: No transaction integrity checks
    $_SESSION['cart'] = array();
    $message = "Order placed successfully! (No actual processing done - this is a demo)";
}

// Get cart items with product details
$cart_items = array();
$total = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // VULNERABILITY: SQL Injection via product_id from session
        // But first check if product_id is valid to prevent SQL errors
        if (!empty($product_id) && is_numeric($product_id)) {
            $query = "SELECT * FROM products WHERE id = $product_id";
            $result = $conn->query($query);
            
            if ($result && $product = $result->fetch_assoc()) {
                $product['cart_quantity'] = $quantity;
                $product['subtotal'] = $product['price'] * $quantity;
                $total += $product['subtotal'];
                $cart_items[] = $product;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>VulnShop - Shopping Cart</title>
    <meta charset="utf-8">
    <!-- VULNERABILITY: Vulnerable & Outdated Components -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: #333; color: white; padding: 15px; margin-bottom: 20px; }
        .nav a { color: white; text-decoration: none; margin-right: 20px; }
        .nav a:hover { text-decoration: underline; }
        .cart-container { background: white; padding: 30px; border-radius: 5px; border: 1px solid #ddd; }
        .cart-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .cart-table th, .cart-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .cart-table th { background: #f8f9fa; font-weight: bold; }
        .btn { background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin-right: 10px; }
        .btn:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .total-row { font-weight: bold; font-size: 1.2em; background: #f8f9fa; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .message.success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .empty-cart { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VulnShop - Shopping Cart</h1>
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

    <div class="cart-container">
        <h2>Your Shopping Cart</h2>
        
        <?php if (isset($message)): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <h3>Your cart is empty</h3>
                <p><a href="index.php" class="btn">Continue Shopping</a></p>
            </div>
        <?php else: ?>
            <!-- VULNERABILITY: No CSRF protection -->
            <form method="POST">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php 
                                        // VULNERABILITY: XSS - No output escaping
                                        echo $item['name']; 
                                    ?></strong><br>
                                    <small><?php echo substr($item['description'], 0, 100); ?>...</small>
                                </td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <!-- VULNERABILITY: No validation on quantity input -->
                                    <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                           value="<?php echo $item['cart_quantity']; ?>" 
                                           min="-1000" max="1000" style="width: 80px;">
                                    <br>
                                    <small>Available: <?php echo $item['stock']; ?></small>
                                    <?php if ($item['cart_quantity'] > $item['stock']): ?>
                                        <br><small style="color: red;">⚠️ Not enough stock!</small>
                                    <?php endif; ?>
                                    <?php if ($item['cart_quantity'] < 0): ?>
                                        <br><small style="color: orange;">⚠️ Negative quantity!</small>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                <td>
                                    <a href="?remove=<?php echo $item['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Remove this item?')">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="3">Total:</td>
                            <td>$<?php echo number_format($total, 2); ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" name="update_cart" class="btn">Update Cart</button>
                    <button type="submit" name="checkout" class="btn btn-success" 
                            onclick="return confirm('Place order for $<?php echo number_format($total, 2); ?>?')">
                        Checkout ($<?php echo number_format($total, 2); ?>)
                    </button>
                </div>
            </form>
        <?php endif; ?>
        
        <p><a href="index.php" class="btn">← Continue Shopping</a></p>
    </div>

    <!-- VULNERABILITY: Information Disclosure - Debug cart data -->
    <?php if (isset($_GET['debug'])): ?>
        <div style="background: #f8f9fa; padding: 20px; margin-top: 20px; border: 1px solid #dee2e6; border-radius: 5px;">
            <h4>Debug Information:</h4>
            <pre>Cart Session Data: <?php print_r($_SESSION['cart']); ?></pre>
            <pre>POST Data: <?php print_r($_POST); ?></pre>
            <pre>Total Calculation: <?php echo $total; ?></pre>
        </div>
    <?php endif; ?>

    <script>
        // VULNERABILITY: Client-side validation that can be bypassed
        $(document).ready(function() {
            $('input[name^="quantities"]').on('change', function() {
                var quantity = parseInt($(this).val());
                var row = $(this).closest('tr');
                
                if (quantity < 0) {
                    // VULNERABILITY: Warning only, doesn't prevent negative quantities
                    console.log('Warning: Negative quantity detected!');
                }
                
                // VULNERABILITY: Stock check is client-side only
                var availableText = $(this).siblings('small').first().text();
                var available = parseInt(availableText.replace('Available: ', ''));
                
                if (quantity > available) {
                    alert('Warning: Quantity exceeds available stock!');
                }
            });
        });
    </script>
</body>
</html>