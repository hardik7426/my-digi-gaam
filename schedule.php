<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
function format_time_gujarati($time24) {
    if (empty($time24)) return '';
    list($hour, $minutes) = explode(':', $time24); $hour = (int)$hour;
    $period = '';
    if ($hour >= 21 || $hour < 4) { $period = 'રાત્રે'; }
    elseif ($hour >= 17) { $period = 'સાંજે'; }
    elseif ($hour >= 12) { $period = 'બપોરે'; }
    else { $period = 'સવારે'; }
    $hour12 = $hour % 12;
    if ($hour12 == 0) { $hour12 = 12; }
    return sprintf('%s %02d:%s', $period, $hour12, $minutes);
}
$buses = $conn->query("SELECT * FROM schedules WHERE type = 'બસ' ORDER BY arrival_time");
$trains = $conn->query("SELECT * FROM schedules WHERE type = 'ટ્રેન' ORDER BY arrival_time");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>બસ અને ટ્રેનનું સમયપત્રક - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --header-bg: #ffffff; --card-bg: rgba(255, 255, 255, 0.9); --primary-text: #1a202c; --secondary-text: #718096; --accent-color-1: #3182ce; --accent-color-2: #38b2ac; --shadow-color: rgba(0, 0, 0, 0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/index.jpeg'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; transition: all 0.3s ease; padding: 8px 15px; border-radius: 8px; border: 1px solid transparent; }
        .main-header a.back-link:hover { background-color: #f7fafc; color: var(--primary-text); border-color: #e2e8f0; }

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
        }
        .search-wrapper { position: relative; flex-grow: 1; }
        #searchInput { width: 100%; padding: 12px 20px 12px 45px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 1rem; font-family: 'Noto Sans Gujarati', sans-serif; }
        .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--secondary-text); }
        .filter-buttons { display: flex; gap: 10px; }
        .filter-btn { padding: 12px 20px; border: 1px solid #e2e8f0; background-color: white; border-radius: 8px; font-family: 'Noto Sans Gujarati', sans-serif; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background-color 0.3s, color 0.3s; }
        .filter-btn.active { background-color: var(--accent-color-1); color: white; border-color: var(--accent-color-1); }

        .schedule-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; }
        .schedule-card { background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; padding: 20px 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); border: 1px solid rgba(255, 255, 255, 0.3); transition: transform 0.3s ease; }
        .schedule-card h3 { font-size: 1.15rem; font-weight: 700; color: var(--primary-text); margin-bottom: 15px; }
        .time-info { display: flex; justify-content: space-between; align-items: center; font-size: 1rem; color: var(--secondary-text); }
        .time-info span { font-weight: 600; color: var(--primary-text); }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; font-weight: 500; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-clock"></i> બસ અને ટ્રેનનું સમયપત્રક</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="content-container">
            <div class="controls-container">
                <div class="search-wrapper">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="નામ દ્વારા શોધો...">
                </div>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">બધા</button>
                    <button class="filter-btn" data-filter="બસ">બસ</button>
                    <button class="filter-btn" data-filter="ટ્રેન">ટ્રેન</button>
                </div>
            </div>

            <div class="schedule-grid">
                <?php while($bus = $buses->fetch_assoc()): ?>
                <div class="schedule-card" data-type="બસ" data-name="<?php echo htmlspecialchars($bus['name']); ?>">
                    <h3><?php echo htmlspecialchars($bus['name']); ?></h3>
                    <div class="time-info">
                        <p>આવવાનો સમય: <span><?php echo format_time_gujarati($bus['arrival_time']); ?></span></p>
                        <p>ઉપડવાનો સમય: <span><?php echo format_time_gujarati($bus['departure_time']); ?></span></p>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php while($train = $trains->fetch_assoc()): ?>
                <div class="schedule-card" data-type="ટ્રેન" data-name="<?php echo htmlspecialchars($train['name']); ?>">
                    <h3><?php echo htmlspecialchars($train['name']); ?></h3>
                    <div class="time-info">
                        <p>આવવાનો સમય: <span><?php echo format_time_gujarati($train['arrival_time']); ?></span></p>
                        <p>ઉપડવાનો સમય: <span><?php echo format_time_gujarati($train['departure_time']); ?></span></p>
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
            const filterButtons = document.querySelectorAll('.filter-btn');
            const scheduleCards = document.querySelectorAll('.schedule-card');

            function filterAndSearch() {
                const searchTerm = searchInput.value.toLowerCase();
                const activeFilter = document.querySelector('.filter-btn.active').dataset.filter;

                scheduleCards.forEach(card => {
                    const type = card.dataset.type;
                    const name = card.dataset.name.toLowerCase();

                    const typeMatch = (activeFilter === 'all') || (type === activeFilter);
                    const searchMatch = name.includes(searchTerm);

                    if (typeMatch && searchMatch) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('keyup', filterAndSearch);

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    filterAndSearch();
                });
            });
        });
    </script>
</body>
</html>