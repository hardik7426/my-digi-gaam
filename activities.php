<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$activities = $conn->query("SELECT * FROM activities ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>આધ્યાત્મિક-વિકાસ પ્રવૃત્તિઓ - માય ડિજી ગામ</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* Base Styles */
        :root {
            --header-bg: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.9);
            --primary-text: #1a202c;
            --secondary-text: #718096;
            --accent-color-1: #3182ce;
            --accent-color-2: #38b2ac;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        /* === BODY STYLE UPDATED WITH BACKGROUND IMAGE === */
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
        main {
            flex-grow: 1;
        }

        /* Header Styling */
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

        /* Activity Grid Layout */
        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            width: 100%;
        }

        /* === ACTIVITY CARD UPDATED WITH GLASS EFFECT === */
        .activity-card {
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .activity-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .card-header { padding: 20px 25px; }
        .card-header h3 { font-size: 1.25rem; font-weight: 700; color: var(--primary-text); margin: 0; }
        .card-body { padding: 0 25px 25px; flex-grow: 1; }
        .card-body ul { list-style: none; padding: 0; }
        .card-body li { display: flex; align-items: center; margin-bottom: 15px; color: var(--secondary-text); font-size: 0.95rem; }
        .card-body i { font-size: 1.1rem; color: var(--accent-color-1); width: 30px; text-align: center; margin-right: 10px; }
        .card-body strong { color: var(--primary-text); font-weight: 500; }
        .card-footer { background-color: #f0f9ff; padding: 15px 25px; text-align: center; border-top: 1px solid #e2e8f0; }
        .contact-link { color: var(--accent-color-2); text-decoration: none; font-weight: 600; font-size: 1rem; transition: color 0.3s ease; }
        .contact-link:hover { color: #2c7a7b; }
        .contact-link i { margin-right: 8px; }

        /* Footer CSS */
        .footer {
            background-color: #2d3748;
            color: #a0aec0;
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
            font-size: 0.9rem;
        }
        .footer strong { color: #ffffff; font-weight: 500; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-hands-praying"></i> આધ્યાત્મિક-વિકાસ પ્રવૃત્તિઓ</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="activity-grid">
            <?php while($row = $activities->fetch_assoc()): ?>
                <div class="activity-card">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>
                                <i class="fa-solid fa-user-tie"></i>
                                <strong>આયોજક:</strong>&nbsp;<?php echo htmlspecialchars($row['organizer']); ?>
                            </li>
                            <li>
                                <i class="fa-solid fa-calendar-days"></i>
                                <strong>સમયપત્રક:</strong>&nbsp;<?php echo htmlspecialchars($row['schedule_day']); ?>, <?php echo htmlspecialchars($row['schedule_time']); ?>
                            </li>
                            <li>
                                <i class="fa-solid fa-map-marker-alt"></i>
                                <strong>સ્થળ:</strong>&nbsp;<?php echo htmlspecialchars($row['location']); ?>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="tel:<?php echo htmlspecialchars($row['contact']); ?>" class="contact-link">
                            <i class="fa-solid fa-phone"></i>
                            સંપર્ક: <?php echo htmlspecialchars($row['contact']); ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
    
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>