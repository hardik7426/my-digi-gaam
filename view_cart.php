<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Fetch items in the cart for this user
$stmt = $conn->prepare("SELECT o.id as order_id, p.id as product_id, p.name, p.price, p.image 
                       FROM stationery_orders o 
                       JOIN stationery_products p ON o.product_id = p.id 
                       WHERE o.user_id = ? AND o.order_status = 'In Cart'
                       ORDER BY o.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

$total_price = 0;
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>મારો કાર્ટ - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --header-bg: #ffffff; --card-bg: rgba(255, 255, 255, 0.92); --primary-text: #1a202c; --secondary-text: #718096; --accent-color-1: #3182ce; --accent-color-2: #38b2ac; --danger-color: #e53e3e; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/index.jpeg'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: flex; justify-content: space-between; align-items: center; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        
        .content-container { max-width: 900px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .cart-item { display: flex; align-items: center; background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .cart-item img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-right: 20px; }
        .cart-item-info { flex-grow: 1; }
        .cart-item-info h3 { font-size: 1.3rem; }
        .cart-item-info .price { font-size: 1.1rem; font-weight: 600; color: var(--accent-color-2); }
        .cart-item-remove { color: var(--danger-color); text-decoration: none; font-size: 1.2rem; }
        .cart-total { background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; padding: 30px; margin-top: 30px; text-align: right; }
        .cart-total h2 { font-size: 1.8rem; margin-bottom: 10px; }
        .no-items { text-align: center; color: white; font-size: 1.2rem; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-shopping-cart"></i> મારો કાર્ટ</h1>
        <a href="stationery_store.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ખરીદી ચાલુ રાખો</a>
    </header>
    <main>
        <div class="content-container">
            <?php if ($cart_items->num_rows > 0): ?>
                <?php while($item = $cart_items->fetch_assoc()): ?>
                    <div class="cart-item">
                        <img src="uploads/stationery/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="cart-item-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <span class="price">₹<?php echo htmlspecialchars($item['price']); ?></span>
                        </div>
                        <a href="remove_from_cart.php?id=<?php echo $item['order_id']; ?>" class="cart-item-remove" onclick="return confirm('શું તમે આ આઇટમ કાર્ટમાંથી દૂર કરવા માંગો છો?');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                    <?php $total_price += $item['price']; ?>
                <?php endwhile; ?>
                
                <div class="cart-total">
                    <h2>કુલ કિંમત: ₹<?php echo $total_price; ?></h2>
                </div>
            <?php else: ?>
                <p class="no-items">તમારો કાર્ટ ખાલી છે.</p>
            <?php endif; ?>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>Hardik , Dhiraj , Nihar</strong>
    </footer>
</body>
</html>