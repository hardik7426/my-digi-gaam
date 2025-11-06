<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: medical_stores.php');
    exit();
}
$store_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM medical_stores WHERE id = ?");
$stmt->bind_param("i", $store_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Medical store not found.");
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - મેડિકલ વિગત</title>
    
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
            max-width: 1000px;
            margin: 40px auto;
            padding: 40px;
            width: 90%;
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            align-items: center;
        }
        .detail-photo img {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            object-fit: cover;
            aspect-ratio: 1 / 1;
        }
        .detail-info h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--primary-text);
        }
        .info-list {
            list-style: none;
            padding: 0;
        }
        .info-list li {
            display: flex;
            font-size: 1.1rem;
            color: var(--secondary-text);
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-list li:last-child {
            border-bottom: none;
        }
        .info-list li i {
            font-size: 1.2rem;
            color: var(--accent-color-1);
            width: 35px;
            padding-top: 4px;
        }
        .info-list li strong {
            color: var(--primary-text);
            font-weight: 600;
            margin-right: 8px;
        }
        .phone-link {
            text-decoration: none;
            background: linear-gradient(135deg, var(--accent-color-1), var(--accent-color-2));
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
            margin-top: 20px;
            transition: transform 0.2s, box-shadow 0.3s;
        }
        .phone-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .phone-link i {
            margin-right: 10px;
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
        
        @media (max-width: 768px) {
            .detail-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-pills"></i> મેડિકલ વિગત</h1>
        <a href="medical_stores.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <div class="detail-layout">
                <div class="detail-photo">
                    <img src="uploads/medicals/<?php echo htmlspecialchars($product['photo'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x400?text=No+Image';">
                </div>
                <div class="detail-info">
                    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                    <ul class="info-list">
                        <li><i class="fa-solid fa-user"></i><strong>માલિક:</strong> <?php echo htmlspecialchars($product['owner_name']); ?></li>
                        <li><i class="fa-solid fa-location-dot"></i><strong>સ્થળ:</strong> <?php echo htmlspecialchars($product['location']); ?></li>
                        <li><i class="fa-solid fa-clock"></i><strong>સમય:</strong> <?php echo htmlspecialchars(date("h:i A", strtotime($product['open_time']))); ?> - <?php echo htmlspecialchars(date("h:i A", strtotime($product['close_time']))); ?></li>
                        <li><i class="fa-solid fa-phone"></i><strong>સંપર્ક:</strong> <?php echo htmlspecialchars($product['contact']); ?></li>
                    </ul>
                    <a href="tel:<?php echo htmlspecialchars($product['contact']); ?>" class="phone-link"><i class="fa-solid fa-phone"></i> હમણાં કૉલ કરો</a>
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