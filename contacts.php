<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$contacts = $conn->query("SELECT * FROM contacts ORDER BY id");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>મહત્વના સંપર્ક - માય ડિજી ગામ</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* Base Styles */
        :root {
            --header-bg: #ffffff;
            --card-bg: #ffffff;
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

        main {
            flex-grow: 1; /* Pushes footer down */
        }
        
        /* === CONTENT CONTAINER UPDATED WITH GLASS EFFECT === */
        .content-container {
            max-width: 900px;
            margin: 40px auto;
            width: 100%;
            padding: 30px 40px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        /* Modern Table Styling */
        table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
        thead { display: none; }
        tbody tr { background: var(--card-bg); border-radius: 12px; box-shadow: 0 4px 6px -1px var(--shadow-color); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        tbody tr:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px var(--shadow-color); }
        td { padding: 20px 25px; vertical-align: middle; }
        td:first-child { font-weight: 700; font-size: 1.1rem; color: var(--primary-text); border-left: 5px solid var(--accent-color-1); border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        td:nth-child(2) { color: var(--secondary-text); font-size: 1rem; }
        td:last-child { text-align: right; border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
        
        .phone-link {
            background-color: var(--accent-color-2);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        .phone-link i { margin-right: 8px; }
        .phone-link:hover { background-color: #2c7a7b; }

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
        <h1><i class="fa-solid fa-address-book"></i> મહત્વના સંપર્ક</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>
    
    <main>
        <div class="content-container">
            <table>
                <thead>
                    <tr><th>હોદ્દો</th><th>નામ</th><th>ફોન નંબર</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $contacts->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['designation']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td>
                            <a href="tel:<?php echo htmlspecialchars($row['phone']); ?>" class="phone-link">
                                <i class="fa-solid fa-phone"></i>
                                <?php echo htmlspecialchars($row['phone']); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>