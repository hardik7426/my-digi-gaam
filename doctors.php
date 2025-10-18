<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$doctors = $conn->query("SELECT * FROM doctors ORDER BY name");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ડોક્ટરોની યાદી - માય ડિજી ગામ</title>

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
        .doctor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        .doctor-card {
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
        .doctor-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .doctor-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .doctor-info {
            padding: 20px;
        }
        .doctor-info .specialization {
            font-size: 1rem;
            font-weight: 600;
            color: var(--accent-color-1);
            margin-bottom: 5px;
        }
        .doctor-info h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .view-btn {
            display: block;
            background: var(--accent-color-2);
            color: white;
            text-align: center;
            padding: 12px;
            text-decoration: none;
            font-weight: 600;
            margin-top: auto; /* Pushes button to the bottom */
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
        <h1><i class="fa-solid fa-user-doctor"></i> ડોક્ટરોની યાદી</h1>
        <a href="hospital.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <div class="doctor-grid">
                <?php while($doc = $doctors->fetch_assoc()): ?>
                <div class="doctor-card">
                    <img src="uploads/doctors/<?php echo htmlspecialchars($doc['photo'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($doc['name']); ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x300?text=No+Photo';">
                    <div class="doctor-info">
                        <span class="specialization"><?php echo htmlspecialchars($doc['specialization']); ?></span>
                        <h3><?php echo htmlspecialchars($doc['name']); ?></h3>
                    </div>
                    <a href="doctor_detail.php?id=<?php echo $doc['id']; ?>" class="view-btn">વિગતો જુઓ</a>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>