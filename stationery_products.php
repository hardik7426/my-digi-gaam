<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_GET['category_id']) || !is_numeric($_GET['category_id'])) {
    header('Location: stationery_store.php');
    exit();
}
$category_id = (int)$_GET['category_id'];

// Get category name securely
$stmt_cat = $conn->prepare("SELECT name FROM stationery_categories WHERE id = ?");
$stmt_cat->bind_param("i", $category_id);
$stmt_cat->execute();
$category_result = $stmt_cat->get_result();
$category = $category_result->fetch_assoc();
$category_name = $category ? $category['name'] : 'અજાણી કેટેગરી';

// Get all products for this category securely
$stmt_prod = $conn->prepare("SELECT * FROM stationery_products WHERE category_id = ? ORDER BY name");
$stmt_prod->bind_param("i", $category_id);
$stmt_prod->execute();
$products = $stmt_prod->get_result();
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_name); ?> - સ્ટેશનરી</title>
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
        .content-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; width: 100%; }
        
        /* === NEW FILTER & SEARCH BAR STYLING === */
        .controls-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
        }
        .search-wrapper { position: relative; flex-grow: 1; }
        #searchInput { width: 100%; padding: 12px 20px 12px 45px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 1rem; font-family: 'Noto Sans Gujarati', sans-serif; }
        .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--secondary-text); }
        .filter-group { display: flex; align-items: center; gap: 10px; }
        .filter-group label { font-weight: 500; }
        #priceFilter { padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: 'Noto Sans Gujarati', sans-serif; font-size: 1rem; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
        .product-card { background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; text-decoration: none; color: var(--primary-text); box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: transform 0.3s ease; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.3); display: flex; flex-direction: column; }
        .product-card:hover { transform: translateY(-8px); }
        .product-card img { width: 100%; height: 220px; object-fit: cover; }
        .product-info { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .product-info h4 { font-size: 0.9rem; color: var(--secondary-text); font-weight: 500; margin-bottom: 5px; }
        .product-info h3 { font-size: 1.2rem; font-weight: 600; margin-bottom: auto; }
        .product-footer { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-top: 1px solid rgba(255,255,255,0.4); }
        .price { font-size: 1.4rem; font-weight: 700; color: var(--accent-color-2); }
        .view-btn { text-decoration: none; background-color: var(--accent-color-1); color: white; padding: 8px 15px; border-radius: 8px; font-weight: 500; transition: background-color 0.3s; }
        .view-btn:hover { background-color: #2b6cb0; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-shapes"></i> <?php echo htmlspecialchars($category_name); ?></h1>
        <a href="stationery_store.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> બધી કેટેગરી પર પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <div class="controls-container">
                <div class="search-wrapper">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="પ્રોડક્ટનું નામ શોધો...">
                </div>
                <div class="filter-group">
                    <label for="priceFilter">કિંમત પ્રમાણે:</label>
                    <select id="priceFilter">
                        <option value="default">ડિફૉલ્ટ</option>
                        <option value="low-to-high">ઓછી થી વધુ</option>
                        <option value="high-to-low">વધુ થી ઓછી</option>
                    </select>
                </div>
            </div>

            <div class="product-grid">
                <?php while($product = $products->fetch_assoc()): ?>
                <div class="product-card" data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>" data-price="<?php echo htmlspecialchars($product['price']); ?>">
                    <img src="uploads/stationery/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/300x220?text=No+Image';">
                    <div class="product-info">
                        <h4><?php echo htmlspecialchars($product['company']); ?></h4>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    </div>
                    <div class="product-footer">
                        <span class="price">₹<?php echo htmlspecialchars($product['price']); ?></span>
                        <a href="stationery_product_detail.php?id=<?php echo $product['id']; ?>" class="view-btn">વિગતો જુઓ</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const priceFilter = document.getElementById('priceFilter');
            const productGrid = document.querySelector('.product-grid');
            const productCards = Array.from(productGrid.querySelectorAll('.product-card'));

            function filterAndSortProducts() {
                const searchTerm = searchInput.value.toLowerCase();
                const sortOrder = priceFilter.value;

                // First, filter based on search term
                const filteredCards = productCards.filter(card => {
                    const name = card.dataset.name;
                    return name.includes(searchTerm);
                });

                // Then, sort the filtered cards
                filteredCards.sort((a, b) => {
                    const priceA = parseFloat(a.dataset.price);
                    const priceB = parseFloat(b.dataset.price);
                    if (sortOrder === 'low-to-high') {
                        return priceA - priceB;
                    } else if (sortOrder === 'high-to-low') {
                        return priceB - priceA;
                    }
                    return 0; // 'default' order
                });
                
                // Hide all cards first
                productCards.forEach(card => card.style.display = 'none');
                
                // Append the sorted and filtered cards back to the grid
                filteredCards.forEach(card => {
                    card.style.display = 'flex';
                    productGrid.appendChild(card);
                });
            }

            searchInput.addEventListener('keyup', filterAndSortProducts);
            priceFilter.addEventListener('change', filterAndSortProducts);
        });
    </script>
</body>
</html>