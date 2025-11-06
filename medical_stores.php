<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$stores = $conn->query("SELECT * FROM medical_stores ORDER BY name");

// Helper function to format time
function format_time_12hr($time24) {
    if (empty($time24)) return '';
    $time = strtotime($time24);
    return date("h:i A", $time);
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>મેડિકલ સ્ટોર્સ - માય ડિજી ગામ</title>
    
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

        .main-header {
            background-color: var(--header-bg);
            padding: 1rem 2.5rem;
            box-shadow: 0 4px 6px -1px var(--shadow-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link {
            color: var(--secondary-text);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 8px;
            border: 1px solid transparent;
        }
        .main-header a.back-link:hover {
            background-color: #f7fafc;
            color: var(--primary-text);
            border-color: #e2e8f0;
        }

        .content-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            width: 100%;
        }
        .store-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        .store-card {
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        .store-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .store-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .store-info {
            padding: 20px;
            flex-grow: 1;
        }
        .store-info h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .store-info p {
            color: var(--secondary-text);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            font-size: 1rem;
        }
        .store-info i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: var(--accent-color-1);
        }
        .view-btn {
            display: block;
            background: var(--accent-color-2);
            color: white;
            text-align: center;
            padding: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .view-btn:hover {
            background-color: #2c7a7b; /* Darker Teal */
        }
        
        .footer {
            background-color: #2d3748;
            color: #a0aec0;
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
            font-size: 0.9rem;
        }
        .footer strong {
            color: #ffffff;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-pills"></i> મેડિકલ સ્ટોર્સ</h1>
        <a href="hospital.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <div class="store-grid">
                <?php while($store = $stores->fetch_assoc()): ?>
                <div class="store-card">
                    <img src="uploads/medicals/<?php echo htmlspecialchars($store['photo'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($store['name']); ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x250?text=No+Image';">
                    <div class="store-info">
                        <h3><?php echo htmlspecialchars($store['name']); ?></h3>
                        <p><i class="fa-solid fa-location-dot"></i><?php echo htmlspecialchars($store['location']); ?></p>
                        <p><i class="fa-solid fa-clock"></i><?php echo format_time_12hr($store['open_time']); ?> - <?php echo format_time_12hr($store['close_time']); ?></p>
                    </div>
                    <a href="medical_detail.php?id=<?php echo $store['id']; ?>" class="view-btn">સંપૂર્ણ વિગતો જુઓ</a>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>Hardik , Dhiraj , Nihar</strong>
    </footer>
</body>
</html>