<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// The PHP submission logic is handled by submit_complaint_ajax.php
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ફરીયાદ કરો - માય ડિજી ગામ</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        /* Professional CSS Styles Embedded Directly in the File */
        :root {
            --header-bg: #ffffff;
            --primary-text: #1a202c;
            --secondary-text: #718096;
            --accent-color-1: #3182ce;
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
            max-width: 800px;
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
        
        /* Attractive Form Styling */
        .complaint-form p { 
            color: var(--secondary-text); 
            margin-bottom: 25px; 
            font-size: 0.9rem;
            border-left: 3px solid var(--accent-color-1);
            padding-left: 15px;
            background-color: #f7fafc;
            padding-top: 10px;
            padding-bottom: 10px;
            border-radius: 0 8px 8px 0;
        }
        .complaint-form label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px; 
            font-size: 1rem;
            color: var(--primary-text); 
        }
        .complaint-form input[type="text"],
        .complaint-form input[type="tel"] {
            width: 100%;
            padding: 14px;
            margin-bottom: 25px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-size: 1rem;
            font-family: 'Noto Sans Gujarati', sans-serif;
        }
        .complaint-form input:focus {
            border-color: var(--accent-color-1);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
            outline: none;
        }
        .complaint-form button {
            display: inline-block;
            width: 100%;
            padding: 14px 30px;
            background-color: var(--accent-color-1);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
        }
        .complaint-form button:hover { 
            background-color: #2b6cb0;
            transform: translateY(-2px);
        }
        
        /* AJAX Message Styling */
        #ajax-message {
            text-align: center;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            display: none;
        }
        #ajax-message.success { color: #2f855a; background-color: #c6f6d5; }
        #ajax-message.error { color: #c53030; background-color: #fed7d7; }
        
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
        <h1><i class="fa-solid fa-bullhorn"></i> ફરીયાદ નોંધાવો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="content-container">
            <div id="ajax-message"></div>
            
            <form class="complaint-form" id="complaintForm">
                <p>*Note: નામ / શેરી ની વિગત વગર ની કોઈ બિનજરૂરી ફરિયાદનો નિકાલ આવવાની શક્યતા ઓછી છે એટલે ખાસ લખજો.</p>
                
                <label for="name">તમારું નામ *</label>
                <input type="text" id="name" name="name" required>
                
                <label for="street">શેરીની વિગત/નામ *</label>
                <input type="text" id="street" name="street" placeholder="ઉદાહરણ તરીકે: પ્રાણેશ્વર ગરબી ચોકની બાજુમાં" required>
                
                <label for="mobile">મોબાઈલ નંબર</label>
                <input type="tel" id="mobile" name="mobile">
                
                <button type="submit">ફરીયાદ સબમિટ કરો</button>
            </form>
        </div>
    </main>

    <script>
    $(document).ready(function() {
        $('#complaintForm').on('submit', function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: 'submit_complaint_ajax.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#ajax-message').html(response.message).removeClass('error').addClass('success').show();
                        $('#complaintForm')[0].reset();
                    } else {
                        $('#ajax-message').html(response.message).removeClass('success').addClass('error').show();
                    }
                },
                error: function() {
                    $('#ajax-message').html('સર્વર સાથે કનેક્ટ થઈ શક્યું નથી.').removeClass('success').addClass('error').show();
                }
            });
        });
    });
    </script>
    
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>