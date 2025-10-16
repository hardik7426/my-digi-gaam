<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$issues = $conn->query("SELECT * FROM electricity_issues ORDER BY id");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>લાઈટ - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --header-bg: #ffffff; --card-bg: rgba(255, 255, 255, 0.9); --primary-text: #1a202c; --secondary-text: #718096; --accent-color-1: #3182ce; --status-open-bg: #fffaf0; --status-open-text: #dd6b20; --status-closed-bg: #f0fff4; --status-closed-text: #38a169; --shadow-color: rgba(0, 0, 0, 0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/index.jpeg'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        .content-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .search-container { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); padding: 20px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .search-wrapper { position: relative; }
        #searchInput { width: 100%; padding: 15px 20px 15px 50px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 1.1rem; font-family: 'Noto Sans Gujarati', sans-serif; }
        .search-wrapper i { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: var(--secondary-text); }
        .issue-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        .issue-card { background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; padding: 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); border: 1px solid rgba(255, 255, 255, 0.3); display: flex; flex-direction: column; transition: transform 0.3s ease; }
        .issue-card:hover { transform: translateY(-5px); }
        .issue-id { font-size: 1.5rem; font-weight: 700; color: var(--accent-color-1); margin-bottom: 15px; }
        .assigned-person { font-size: 1rem; color: var(--secondary-text); margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
        .assigned-person i { margin-right: 8px; }
        .status-badge { align-self: flex-start; padding: 8px 15px; border-radius: 20px; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; }
        .status-badge i { margin-right: 8px; }
        .status-open { background-color: var(--status-open-bg); color: var(--status-open-text); }
        .status-closed { background-color: var(--status-closed-bg); color: var(--status-closed-text); }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-lightbulb"></i> લાઈટ / વીજળીના થાંભલા</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <div class="search-container">
                <div class="search-wrapper">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="ID અથવા સોંપેલ વ્યક્તિ દ્વારા શોધો...">
                </div>
            </div>
            <div class="issue-grid">
                <?php while($row = $issues->fetch_assoc()): ?>
                    <?php
                        $is_closed = ($row['status'] === 'બંધ');
                        $status_class = $is_closed ? 'status-closed' : 'status-open';
                        $status_icon = $is_closed ? 'fa-solid fa-circle-check' : 'fa-solid fa-triangle-exclamation';
                    ?>
                    <div class="issue-card" data-id="<?php echo htmlspecialchars(strtolower($row['complaint_id'])); ?>" data-assigned="<?php echo htmlspecialchars(strtolower($row['assigned_to'])); ?>">
                        <h3 class="issue-id"><?php echo htmlspecialchars($row['complaint_id']); ?></h3>
                        <p class="assigned-person">
                            <i class="fa-solid fa-user"></i>
                            <strong>સોંપેલ:</strong> <?php echo htmlspecialchars($row['assigned_to']); ?>
                        </p>
                        <div class="status-badge <?php echo $status_class; ?>">
                            <i class="<?php echo $status_icon; ?>"></i>
                            <?php echo htmlspecialchars($row['status']); ?>
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
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const issueCards = document.querySelectorAll('.issue-card');

            issueCards.forEach(card => {
                const id = card.dataset.id;
                const assigned = card.dataset.assigned;

                if (id.includes(searchTerm) || assigned.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>