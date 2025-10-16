<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$categories = $conn->query("SELECT * FROM stationery_categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ઓનલાઈન સ્ટેશનરી - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --header-bg: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.92);
            --primary-text: #1a202c;
            --secondary-text: #718096;
            --accent-color-1: #3182ce;
            --accent-color-2: #38b2ac;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Noto Sans Gujarati', sans-serif;
            color: var(--primary-text);
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/index.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        
        /* NEW Hero Section for Search */
        .hero-section {
            padding: 50px 20px;
            text-align: center;
        }
        .hero-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            margin-bottom: 20px;
        }
        .search-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        #searchInput {
            width: 100%;
            padding: 15px 20px 15px 50px; /* Space for icon */
            border-radius: 50px; /* Pill shape */
            border: 1px solid rgba(255,255,255,0.5);
            font-size: 1.1rem;
            font-family: 'Noto Sans Gujarati', sans-serif;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .search-container i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-text);
        }

        .content-container { max-width: 1200px; margin: 20px auto; padding: 0 20px; width: 100%; }
        .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; }

        /* NEW Attractive Category Card */
        .category-card {
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            text-decoration: none;
            color: var(--primary-text);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            flex-direction: column;
        }
        .category-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2); }
        .category-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .category-card:hover img {
            transform: scale(1.05);
        }
        .card-content {
            padding: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-grow: 1;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .card-content h3 { font-size: 1.3rem; font-weight: 600; margin: 0; }
        .card-content i { font-size: 1rem; color: var(--accent-color-1); transition: transform 0.3s ease; }
        .category-card:hover i { transform: translateX(5px); }
        
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-store"></i> ઓનલાઈન સ્ટેશનરી</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>
    <main>
        <div class="hero-section">
            <h2>તમને શું જોઈએ છે?</h2>
            <div class="search-container">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="searchInput" placeholder="કેટેગરી શોધો (દા.ત. પેન્સિલ, પુસ્તકો)...">
            </div>
        </div>

        <div class="content-container">
            <div class="category-grid">
                <?php while($category = $categories->fetch_assoc()): ?>
                <a href="stationery_products.php?category_id=<?php echo $category['id']; ?>" class="category-card">
                    <img src="uploads/stationery/<?php echo htmlspecialchars($category['image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/300x200?text=No+Image';">
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>

    <script>
        // Live search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let searchTerm = this.value.toLowerCase();
            let categoryCards = document.querySelectorAll('.category-card');

            categoryCards.forEach(card => {
                let categoryName = card.querySelector('h3').textContent.toLowerCase();
                if (categoryName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>