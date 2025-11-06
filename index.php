<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Array of motivational quotes in Gujarati
$quotes = [
    "સફળતાનો કોઈ રહસ્ય નથી, તે તૈયારી, સખત મહેનત અને નિષ્ફળતામાંથી શીખવાનું પરિણામ છે.",
    "તમારો સમય મર્યાદિત છે, તેથી તેને કોઈ બીજાના જીવન જીવવામાં બગાડો નહીં.",
    "જીવનમાં સૌથી મોટું જોખમ એ છે કે કોઈ જોખમ ન લેવું.",
    "એક સ્વપ્ન જાદુથી વાસ્તવિકતા બનતું નથી; તેમાં પરસેવો, નિશ્ચય અને સખત મહેનત લાગે છે.",
    "શરૂ કરવાનો રસ્તો એ છે કે વાત કરવાનું બંધ કરો અને કરવાનું શરૂ કરો."
];
$random_quote = $quotes[array_rand($quotes)]; // Select a random quote
?>
<!DOCTYPE html>
<html lang="gu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --header-bg: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.95);
            --primary-text: #1a202c;
            --secondary-text: #718096;
            --accent-color-1: #3182ce;
            --accent-color-2: #38b2ac;
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
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/index.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-header {
            background-color: var(--header-bg);
            padding: 1rem 2.5rem;
            box-shadow: 0 4px 6px -1px var(--shadow-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .main-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--accent-color-1);
        }

        .main-header i {
            margin-right: 12px;
        }

        .main-header span a {
            color: var(--secondary-text);
            text-decoration: none;
            font-weight: 500;
        }

        main {
            flex-grow: 1;
        }

        .hero-section {
            padding: 50px 40px;
            text-align: center;
            color: white;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .hero-section h2 {
            font-size: 2.8rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 10px;
        }

        .live-clock {
            font-size: 4.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.6);
            margin-bottom: 20px;
        }

        .quote-of-the-day {
            font-size: 1.1rem;
            font-style: italic;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.9;
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
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.4);
            display: flex;
            flex-direction: column;
            padding: 25px;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .grid-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color-1), var(--accent-color-2));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .grid-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .grid-item:hover::before {
            opacity: 1;
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

        .messenger-link {
            color: var(--accent-color-1);
            text-decoration: none;
            font-weight: 600;
            position: relative;
            margin-right: 15px;
        }

        .notification-dot {
            background-color: #e53e3e;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            line-height: 20px;
            font-size: 0.8rem;
            text-align: center;
            position: absolute;
            top: -10px;
            right: -15px;
        }
    </style>
</head>

<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-mobile-screen-button"></i> માય ડિજી ગામ</h1>
        <span>
            <a href="messenger.php" class="messenger-link">
                <i class="fa-solid fa-bell"></i> મેસેન્જર
                <?php if (isset($_SESSION['unread_messages']) && $_SESSION['unread_messages'] > 0): ?>
                    <span class="notification-dot"><?php echo $_SESSION['unread_messages']; ?></span>
                <?php endif; ?>
            </a>
            | સ્વાગત છે, <?php echo htmlspecialchars($_SESSION['username']); ?>! |
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> લોગઆઉટ</a>
        </span>
    </header>

    <main>
        <div class="hero-section">
            <h2> માંડલ ગામ માં આપનું હાર્દિક સ્વાગત છે</h2>
            <div id="liveClock" class="live-clock"></div>
            <p class="quote-of-the-day">"<?php echo $random_quote; ?>"</p>
        </div>

        <div class="dashboard-grid">
            <a href="contacts.php" class="grid-item">
                <i class="fa-solid fa-address-book"></i>
                <span>મહત્વના સંપર્ક</span>
            </a>
            <a href="schedule.php" class="grid-item">
                <i class="fa-solid fa-bus"></i>
                <span>બસ/ટ્રેનનો સમય</span>
            </a>

            <a href="grants.php" class="grid-item">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <span>ગામની ગ્રાન્ટ</span>
            </a>
            <a href="complaint_form.php" class="grid-item">
                <i class="fa-solid fa-bullhorn"></i>
                <span>ફરીયાદ કરો</span>
            </a>
            <a href="electricity.php" class="grid-item">
                <i class="fa-solid fa-lightbulb"></i>
                <span>લાઈટ</span>
            </a>
            <a href="education.php" class="grid-item">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>શિક્ષણ</span>
            </a>
            <a href="activities.php" class="grid-item">
                <i class="fa-solid fa-hands-praying"></i>
                <span>આધ્યાત્મિક-વિકાસ</span>
            </a>
            <a href="news.php" class="grid-item">
                <i class="fa-solid fa-newspaper"></i>
                <span>ખોરાસા સમાચાર</span>
            </a>
            <a href="documents.php" class="grid-item">
                <i class="fa-solid fa-file-alt"></i>
                <span>જરૂરી દસ્તાવેજ</span>
            </a>
            <a href="stationery_store.php" class="grid-item">
                <i class="fa-solid fa-store"></i>
                <span>ઓનલાઈન સ્ટેશનરી</span>
            </a>
            <a href="hospital.php" class="grid-item">
                <i class="fa-solid fa-hospital"></i>
                <span>હોસ્પિટલ</span>
            </a>
        </div>
    </main>

    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>

    <script>
        function updateClock() {
            const now = new Date();

            // કલાક, મિનિટ અને સેકન્ડ મેળવો
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            // AM કે PM નક્કી કરો
            const ampm = hours >= 12 ? 'PM' : 'AM';

            // 24-કલાકના ફોર્મેટને 12-કલાકના ફોર્મેટમાં ફેરવો
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 વાગ્યાને 12 તરીકે બતાવો

            // જો કલાક એક અંકનો હોય તો આગળ 0 ઉમેરો
            const formattedHours = String(hours).padStart(2, '0');

            // HTML માં સમય અપડેટ કરો
            document.getElementById('liveClock').textContent = `${formattedHours}:${minutes}:${seconds} ${ampm}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>

</html>