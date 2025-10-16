<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// ડેટાબેઝમાંથી રિયલ-ટાઇમ આંકડા મેળવો
$complaints_count = $conn->query("SELECT COUNT(*) as count FROM complaints")->fetch_assoc()['count'];
$contacts_count = $conn->query("SELECT COUNT(*) as count FROM contacts")->fetch_assoc()['count'];
$news_count = $conn->query("SELECT COUNT(*) as count FROM news")->fetch_assoc()['count'];
$grants_count = $conn->query("SELECT COUNT(*) as count FROM grants")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="gu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>એડમિન પેનલ - માય ડિજી ગામ</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --header-bg: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.7);
            --primary-text: #1a202c;
            --secondary-text: #4a5568;
            --accent-color-1: #3182ce;
            --accent-color-2: #38b2ac;
            --accent-color-3: #dd6b20;
            --accent-color-4: #805ad5;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans Gujarati', sans-serif;
            color: var(--primary-text);
            background-image: linear-gradient(rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.6)), url('../assets/images/index.jpeg');
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-header {
            background-color: var(--header-bg);
            backdrop-filter: blur(10px);
            padding: 1rem 2.5rem;
            box-shadow: 0 4px 6px -1px var(--shadow-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .main-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-text);
        }

        .main-header i {
            margin-right: 12px;
            color: var(--accent-color-1);
        }

        .main-header span a {
            color: var(--secondary-text);
            text-decoration: none;
            font-weight: 500;
        }

        main {
            flex-grow: 1;
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* === નવું અને આકર્ષક વેલકમ હેડર === */
        .welcome-header {
            background: linear-gradient(135deg, var(--accent-color-1), var(--accent-color-2));
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .welcome-header h2 {
            font-size: 2.8rem;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
            margin-bottom: 10px;
        }

        .welcome-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* રિયલ-ટાઇમ સ્ટેટ્સ કાર્ડ્સ */
        .stat-overview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .stat-card .icon-wrapper {
            font-size: 2rem;
            padding: 15px;
            border-radius: 50%;
            color: white;
        }

        .stat-card:nth-child(1) .icon-wrapper {
            background: var(--accent-color-1);
        }

        .stat-card:nth-child(2) .icon-wrapper {
            background: var(--accent-color-2);
        }

        .stat-card:nth-child(3) .icon-wrapper {
            background: var(--accent-color-3);
        }

        .stat-card:nth-child(4) .icon-wrapper {
            background: var(--accent-color-4);
        }

        .stat-info .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-text);
        }

        .stat-info .stat-label {
            font-size: 0.9rem;
            color: var(--secondary-text);
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--accent-color-1);
            display: inline-block;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 30px;
        }

        .grid-item {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            text-decoration: none;
            color: var(--primary-text);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
            padding: 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .grid-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .grid-item i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--accent-color-1);
            transition: color 0.3s ease;
        }

        .grid-item:hover i {
            color: var(--accent-color-2);
        }

        .grid-item span {
            font-size: 1.15rem;
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
        <h1><i class="fa-solid fa-user-shield"></i> એડમિન પેનલ</h1>
        <span>સ્વાગત છે, એડમિન! | <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>
                લોગઆઉટ</a></span>
    </header>

    <main>
        <div class="welcome-header">
            <h2 id="greeting">શુભ સવાર, એડમિન!</h2>
            <p id="live-clock-date"></p>
        </div>

        <div class="stat-overview">
            <div class="stat-card">
                <div class="icon-wrapper"><i class="fa-solid fa-bullhorn"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $complaints_count; ?></div>
                    <div class="stat-label">કુલ ફરીયાદો</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-wrapper"><i class="fa-solid fa-address-book"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $contacts_count; ?></div>
                    <div class="stat-label">કુલ સંપર્કો</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-wrapper"><i class="fa-solid fa-newspaper"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $news_count; ?></div>
                    <div class="stat-label">કુલ સમાચાર</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-wrapper"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $grants_count; ?></div>
                    <div class="stat-label">કુલ ગ્રાન્ટ્સ</div>
                </div>
            </div>
        </div>

        <h2 class="section-title">મેનેજમેન્ટ વિભાગ</h2>
        <div class="dashboard-grid">
            <a href="manage_contacts.php" class="grid-item">
                <i class="fa-solid fa-address-book"></i>
                <span>સંપર્ક મેનેજ કરો</span>
            </a>
            <a href="manage_schedules.php" class="grid-item">
                <i class="fa-solid fa-bus"></i>
                <span>સમયપત્રક મેનેજ કરો</span>
            </a>
            <a href="manage_grants.php" class="grid-item">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <span>ગ્રાન્ટ મેનેજ કરો</span>
            </a>
            <a href="manage_complaints.php" class="grid-item">
                <i class="fa-solid fa-bullhorn"></i>
                <span>ફરીયાદો મેનેજ કરો</span>
            </a>
            <a href="manage_electricity.php" class="grid-item">
                <i class="fa-solid fa-lightbulb"></i>
                <span>લાઈટ મેનેજ કરો</span>
            </a>
            <a href="manage_education.php" class="grid-item">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>શિક્ષણ મેનેજ કરો</span>
            </a>
            <a href="manage_activities.php" class="grid-item">
                <i class="fa-solid fa-hands-praying"></i>
                <span>પ્રવૃત્તિઓ મેનેજ કરો</span>
            </a>
            <a href="manage_news.php" class="grid-item">
                <i class="fa-solid fa-newspaper"></i>
                <span>સમાચાર મેનેજ કરો</span>
            </a>
            <a href="manage_documents.php" class="grid-item">
                <i class="fa-solid fa-file-alt"></i>
                <span>દસ્તાવેજ મેનેજ કરો</span>
            </a>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>

    <script>
        function updateClockAndGreeting() {
            const now = new Date();
            const hours = now.getHours();
            let greetingText;

            if (hours < 12) {
                greetingText = "શુભ સવાર, એડમિન!";
            } else if (hours < 18) {
                greetingText = "શુભ બપોર, એડમિન!";
            } else {
                greetingText = "શુભ સાંજ, એડમિન!";
            }
            document.getElementById('greeting').textContent = greetingText;

            const options = {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            };
            document.getElementById('live-clock-date').textContent = now.toLocaleString('gu-IN', options);
        }

        setInterval(updateClockAndGreeting, 1000);
        updateClockAndGreeting();
    </script>
</body>

</html>