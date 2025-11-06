<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: stationery_store.php');
    exit();
}
$product_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM stationery_products p JOIN stationery_categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --header-bg: #ffffff; --card-bg: rgba(255, 255, 255, 0.92); --primary-text: #1a202c; --secondary-text: #718096; --accent-color-1: #3182ce; --accent-color-2: #38b2ac; --shadow-color: rgba(0, 0, 0, 0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/index.jpeg'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        .content-container { max-width: 1000px; margin: 40px auto; padding: 40px; width: 100%; background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); border: 1px solid rgba(255, 255, 255, 0.3); }
        .product-detail-layout { display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; align-items: center; }
        .product-image img { width: 100%; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .product-details h4 { font-size: 1rem; color: var(--secondary-text); font-weight: 500; margin-bottom: 5px; }
        .product-details h2 { font-size: 2.2rem; font-weight: 700; margin-bottom: 15px; }
        .price { font-size: 2.5rem; font-weight: 700; color: var(--accent-color-2); margin-bottom: 20px; }
        .description { font-size: 1rem; line-height: 1.8; color: var(--secondary-text); border-top: 1px solid rgba(0,0,0,0.1); padding-top: 20px; }
        .buy-btn { width: 100%; padding: 15px; margin-top: 30px; background: linear-gradient(135deg, var(--accent-color-1), var(--accent-color-2)); color: white; border: none; border-radius: 8px; font-size: 1.2rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.3s; }
        .buy-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        .buy-btn i { margin-right: 10px; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
        @media (max-width: 768px) { .product-detail-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-box-open"></i> પ્રોડક્ટ વિગત</h1>
        <a href="stationery_products.php?category_id=<?php echo $product['category_id']; ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <div class="product-detail-layout">
                <div class="product-image">
                    <img src="uploads/stationery/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x400?text=No+Image';">
                </div>
                <div class="product-details">
                    <h4><?php echo htmlspecialchars($product['company']); ?></h4>
                    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                    <div class="price">₹<?php echo htmlspecialchars($product['price']); ?></div>
                    <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <button class="buy-btn"><i class="fa-solid fa-shopping-cart"></i> હમણાં ખરીદો</button>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>Hardik , Dhiraj , Nihar</strong>
    </footer>
</body>
</html>