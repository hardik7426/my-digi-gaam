<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$grants = $conn->query("SELECT * FROM grants ORDER BY year DESC, id DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ગામની ગ્રાન્ટ - માય ડિજી ગામ</title>
    
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
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; transition: all 0.3s ease; padding: 8px 15px; border-radius: 8px; border: 1px solid transparent; }
        .main-header a.back-link:hover { background-color: #f7fafc; color: var(--primary-text); border-color: #e2e8f0; }
        .content-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .year-heading { font-size: 2rem; font-weight: 700; color: #ffffff; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); margin-bottom: 25px; padding-left: 15px; border-left: 5px solid var(--accent-color-1); }
        .grant-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin-bottom: 50px; }

        .grant-card {
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .grant-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(var(--accent-color-1), var(--accent-color-2)); transform: scaleY(0); transform-origin: bottom; transition: transform 0.4s ease; }
        .grant-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2); }
        .grant-card:hover::before { transform: scaleY(1); transform-origin: top; }
        .grant-card-horizontal { grid-column: span 2; }
        
        /* === FONT STYLES UPDATED HERE === */
        .grant-description {
            font-size: 1.25rem; /* Increased font size */
            font-weight: 600;   /* Made it bolder */
            color: var(--primary-text);
            line-height: 1.7;
            flex-grow: 1;
            margin-bottom: 20px;
        }
        .donor-info {
            font-size: 1rem;    /* Increased font size */
            color: var(--secondary-text);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .donor-info i { margin-right: 8px; color: var(--accent-color-1); }
        .grant-amount {
            font-size: 1.8rem; /* Increased font size */
            font-weight: 700;
            color: var(--accent-color-2);
            align-self: flex-end;
            padding-top: 15px;
            width: 100%;
            text-align: right;
        }
        .grant-amount i { margin-right: 8px; }

        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; font-weight: 500; }
        
        @media (max-width: 720px) {
            .grant-card-horizontal { grid-column: span 1; }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-hand-holding-dollar"></i> ગામની ગ્રાન્ટ</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="content-container">
            <?php
            $current_year = null;
            $count = 0;
            while($grant = $grants->fetch_assoc()):
                if ($grant['year'] !== $current_year) {
                    if ($current_year !== null) echo '</div>';
                    $current_year = $grant['year'];
                    $count = 0;
                    echo "<h2 class='year-heading'>વર્ષ: {$current_year}</h2>";
                    echo "<div class='grant-grid'>";
                }
                $card_class = ($count == 0) ? 'grant-card grant-card-horizontal' : 'grant-card';
            ?>
                <div class="<?php echo $card_class; ?>">
                    <div>
                        <p class="grant-description"><?php echo htmlspecialchars($grant['project_name']); ?></p>
                        <?php if (!empty($grant['donor_name'])): ?>
                        <div class="donor-info">
                            <i class="fa-solid fa-gift"></i>
                            <strong>દાતા:</strong> <?php echo htmlspecialchars($grant['donor_name']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="grant-amount">
                        <i class="fa-solid fa-indian-rupee-sign"></i>
                        <?php echo number_format($grant['amount']); ?>
                    </div>
                </div>
            <?php
                $count++;
            endwhile;
            if ($current_year !== null) echo '</div>';
            ?>
        </div>
    </main>
    
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>Hardik , Dhiraj , Nihar</strong>
    </footer>
</body>
</html>