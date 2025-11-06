<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$documents = $conn->query("SELECT * FROM documents ORDER BY id");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>જરૂરી દસ્તાવેજ - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        :root { --header-bg: #ffffff; --card-bg: rgba(255, 255, 255, 0.92); --primary-text: #1a202c; --secondary-text: #718096; --accent-color-1: #3182ce; --accent-color-2: #38b2ac; --shadow-color: rgba(0, 0, 0, 0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/index.jpeg'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        .content-container { max-width: 900px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .search-container { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); padding: 20px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .search-wrapper { position: relative; }
        #searchInput { width: 100%; padding: 15px 20px 15px 50px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 1.1rem; font-family: 'Noto Sans Gujarati', sans-serif; }
        .search-wrapper i { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: var(--secondary-text); }
        .accordion-item { background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; margin-bottom: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); border: 1px solid rgba(255, 255, 255, 0.3); }
        .accordion-header { padding: 20px 25px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
        .accordion-header h2 { margin: 0; font-size: 1.3rem; }
        .accordion-header .toggle-icon { transition: transform 0.3s; }
        .accordion-header.active .toggle-icon { transform: rotate(180deg); }
        .accordion-content { display: none; padding: 0 25px 25px; border-top: 1px solid rgba(0,0,0,0.1); }
        .accordion-content p { line-height: 1.8; color: var(--secondary-text); white-space: pre-wrap; padding-top: 20px;}
        .download-link { display: inline-block; margin-top: 20px; text-decoration: none; background-color: var(--accent-color-2); color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; }
        .download-link i { margin-right: 8px; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-file-alt"></i> જરૂરી દસ્તાવેજ</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <div class="search-container">
                <div class="search-wrapper">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="દસ્તાવેજનું નામ શોધો...">
                </div>
            </div>
            
            <div id="accordion-container">
                <?php while($row = $documents->fetch_assoc()): ?>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                        <i class="fa-solid fa-chevron-down toggle-icon"></i>
                    </div>
                    <div class="accordion-content">
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <?php if (!empty($row['pdf_filename'])): ?>
                            <a href="uploads/<?php echo htmlspecialchars($row['pdf_filename']); ?>" class="download-link" download>
                                <i class="fa-solid fa-download"></i> ફોર્મ ડાઉનલોડ કરો
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>Hardik , Dhiraj , Nihar</strong>
    </footer>
    <script>
        $(document).ready(function(){
            // Accordion functionality
            $('.accordion-header').click(function(){
                $(this).toggleClass('active');
                $(this).next('.accordion-content').slideToggle();
            });

            // Live search functionality
            $('#searchInput').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                $('#accordion-container .accordion-item').each(function() {
                    var title = $(this).find('h2').text().toLowerCase();
                    if (title.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
</body>
</html>