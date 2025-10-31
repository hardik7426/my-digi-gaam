<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>હોસ્પિટલ સેવાઓ - માય ડિજી ગામ</title>
    
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
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 30px;
            padding: 40px;
            max-width: 1400px;
            margin: 20px auto;
        }
        .grid-item {
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            text-decoration: none;
            color: var(--primary-text);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.4);
            display: flex;
            flex-direction: column;
            padding: 25px;
            align-items: center;
            text-align: center;
        }
        .grid-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        .grid-item i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--accent-color-1);
        }
        .grid-item span {
            font-size: 1.2rem;
            font-weight: 600;
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
        <h1><i class="fa-solid fa-hospital"></i> હોસ્પિટલ સેવાઓ</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>
    <main>
        <div class="dashboard-grid">
            <a href="doctors.php" class="grid-item">
                <i class="fa-solid fa-user-doctor"></i>
                <span>ડોક્ટરોની યાદી</span>
            </a>
            
            <a href="medicines.php" class="grid-item">
                <i class="fa-solid fa-tablets"></i> <span>દવાઓની માહિતી</span>
            </a>
            <a href="medical_stores.php" class="grid-item">
                <i class="fa-solid fa-pills"></i>
                <span>મેડિકલ સ્ટોર્સ</span>
            </a>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>