<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$contents = $conn->query("SELECT * FROM education_content ORDER BY standard, id DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>શિક્ષણ - માય ડિજી ગામ</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* Base Styles */
        :root {
            --header-bg: #ffffff;
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

        /* === CONTENT CONTAINER UPDATED WITH GLASS EFFECT === */
        .content-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px 40px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            width: 100%;
        }

        /* Attractive Education Card Styling */
        .education-card {
            background: var(--card-bg);
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px -1px var(--shadow-color);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .card-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; background-color: #f7fafc; border-bottom: 1px solid #e2e8f0; }
        .card-header h2 { font-size: 1.3rem; font-weight: 600; margin: 0; }
        .standard-badge { background-color: var(--accent-color-1); color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.9rem; font-weight: 500; }
        .card-body { padding: 25px; font-size: 1rem; line-height: 1.7; color: var(--secondary-text); }
        .card-footer { padding: 20px 25px; background-color: #f7fafc; border-top: 1px solid #e2e8f0; }
        .pdf-actions a { text-decoration: none; color: var(--accent-color-2); font-weight: 600; margin-right: 20px; transition: color 0.3s ease; }
        .pdf-actions a:hover { color: #2c7a7b; }
        .pdf-actions i { margin-right: 8px; }
        
        /* Collapsible PDF Viewer */
        .pdf-viewer { display: none; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .pdf-viewer iframe { width: 100%; height: 600px; border: 1px solid #e2e8f0; border-radius: 8px; }
        
        /* Footer CSS */
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
        <h1><i class="fa-solid fa-graduation-cap"></i> શૈક્ષણિક સામગ્રી</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="content-container">
            <?php while($row = $contents->fetch_assoc()): ?>
                <div class="education-card">
                    <div class="card-header">
                        <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                        <span class="standard-badge">ધોરણ: <?php echo htmlspecialchars($row['standard']); ?></span>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                    </div>
                    
                    <?php if (!empty($row['pdf_filename'])): ?>
                    <div class="card-footer">
                        <div class="pdf-actions">
                            <a href="uploads/<?php echo htmlspecialchars($row['pdf_filename']); ?>" download>
                                <i class="fa-solid fa-download"></i> PDF ડાઉનલોડ કરો
                            </a>
                            <a href="#" class="view-pdf-btn" data-target="pdf-viewer-<?php echo $row['id']; ?>">
                                <i class="fa-solid fa-eye"></i> PDF જુઓ
                            </a>
                        </div>
                        <div class="pdf-viewer" id="pdf-viewer-<?php echo $row['id']; ?>">
                            <iframe src="uploads/<?php echo htmlspecialchars($row['pdf_filename']); ?>"></iframe>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.view-pdf-btn').on('click', function(e) {
                e.preventDefault();
                var targetId = $(this).data('target');
                $('#' + targetId).slideToggle();
            });
        });
    </script>

    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>