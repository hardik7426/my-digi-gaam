<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all unique subjects for filtering
$subjects_result = $conn->query("SELECT DISTINCT subject FROM education_content ORDER BY subject");
$subjects = [];
while($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row['subject'];
}

// Fetch all content, grouped by standard, medium, and category
$contents_result = $conn->query("SELECT * FROM education_content ORDER BY CAST(standard AS UNSIGNED), medium, category, subject");
$education_data = [];
while($row = $contents_result->fetch_assoc()) {
    $education_data[$row['medium']][$row['standard']][$row['category']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>શૈક્ષણિક પોર્ટલ - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; transition: all 0.3s ease; padding: 8px 15px; border-radius: 8px; border: 1px solid transparent; }
        .main-header a.back-link:hover { background-color: #f7fafc; color: var(--primary-text); border-color: #e2e8f0; }
        
        .content-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; width: 100%; }
        
        .filter-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            text-align: center;
        }
        .filter-container h3 { margin-bottom: 15px; font-size: 1.2rem; }
        .filter-buttons { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
        .filter-btn { padding: 10px 18px; border: 1px solid #e2e8f0; background-color: white; border-radius: 20px; font-family: 'Noto Sans Gujarati', sans-serif; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.3s; }
        .filter-btn.active { background-color: var(--accent-color-1); color: white; border-color: var(--accent-color-1); }
        
        .medium-section h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            margin: 40px 0 25px 0;
            padding-left: 15px;
            border-left: 5px solid var(--accent-color-1);
        }
        
        .standard-accordion .standard-header {
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            border-radius: 10px;
            padding: 18px 25px;
            margin-bottom: 1px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255,255,255,0.4);
        }
        .standard-header h3 { margin: 0; font-size: 1.3rem; }
        .standard-header .toggle-icon { transition: transform 0.3s; }
        .standard-header.active .toggle-icon { transform: rotate(180deg); }
        .standard-content {
            display: none;
            padding: 25px;
            background: rgba(255,255,255,0.8);
            border-radius: 0 0 10px 10px;
            margin-bottom: 20px;
        }
        
        .category-section { margin-bottom: 20px; }
        .category-section h4 { font-size: 1.2rem; font-weight: 600; margin-bottom: 15px; color: var(--accent-color-2); padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; }
        .content-list { list-style: none; padding-left: 10px; }
        .content-list li { margin-bottom: 12px; }
        .content-list a { text-decoration: none; color: var(--primary-text); font-weight: 500; transition: color 0.3s; display: flex; align-items: center; }
        .content-list a:hover { color: var(--accent-color-1); }
        .content-list i { margin-right: 12px; color: var(--secondary-text); font-size: 1.1rem; }
        
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; font-weight: 500; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-graduation-cap"></i> શૈક્ષણિક પોર્ટલ</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <?php if (!empty($subjects)): ?>
            <div class="filter-container">
                <h3>વિષય પ્રમાણે ફિલ્ટર કરો</h3>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-subject="all">બધા વિષય</button>
                    <?php foreach($subjects as $subject): ?>
                        <button class="filter-btn" data-subject="<?php echo htmlspecialchars($subject); ?>"><?php echo htmlspecialchars($subject); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php foreach($education_data as $medium => $standards): ?>
            <div class="medium-section">
                <h2><?php echo $medium === 'Gujarati' ? 'ગુજરાતી માધ્યમ' : 'English Medium'; ?></h2>
                <div class="standard-accordion">
                <?php ksort($standards, SORT_NUMERIC); ?>
                <?php foreach($standards as $standard => $categories): ?>
                    <div class="standard-item">
                        <div class="standard-header">
                            <h3>ધોરણ <?php echo $standard; ?></h3>
                            <i class="fa-solid fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="standard-content">
                            <?php foreach($categories as $category => $items): ?>
                            <div class="category-section">
                                <h4><?php if($category == 'Book') echo 'પુસ્તકો (Books)'; elseif($category == 'Navneet') echo 'નવનીત (Digests)'; else echo 'જૂના પ્રશ્નપત્રો'; ?></h4>
                                <ul class="content-list">
                                    <?php foreach($items as $item): ?>
                                    <li data-subject="<?php echo htmlspecialchars($item['subject']); ?>">
                                        <a href="uploads/<?php echo htmlspecialchars($item['pdf_filename']); ?>" target="_blank">
                                            <i class="fa-solid fa-file-pdf"></i>
                                            <span><?php echo htmlspecialchars($item['title']); ?> (<?php echo htmlspecialchars($item['subject']); ?>)</span>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>Hardik , Dhiraj , Nihar</strong>
    </footer>

    <script>
        $(document).ready(function(){
            // Accordion functionality
            $('.standard-header').click(function(){
                $(this).toggleClass('active');
                $(this).next('.standard-content').slideToggle();
            });

            // Subject filter functionality
            $('.filter-btn').click(function(){
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                var selectedSubject = $(this).data('subject');
                
                // Reset visibility for all elements first
                $('.standard-item, .category-section, .content-list li').show();
                
                if (selectedSubject !== 'all') {
                    // Step 1: Hide list items that do not match the selected subject
                    $('.content-list li').each(function() {
                        if ($(this).data('subject') !== selectedSubject) {
                            $(this).hide();
                        }
                    });

                    // Step 2: Hide any category section that is now empty
                    $('.category-section').each(function() {
                        if ($(this).find('li:visible').length === 0) {
                            $(this).hide();
                        }
                    });
                    
                    // Step 3: Hide any standard item that is now empty
                    $('.standard-item').each(function() {
                        if ($(this).find('.category-section:visible').length === 0) {
                            $(this).hide();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>