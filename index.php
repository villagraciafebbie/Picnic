<?php
// index.php - Main entry point
require_once 'config.php';

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    // Validate input
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;
    $color = $_POST['color'] ?? null;
    
    $errors = [];
    
    if (!$date) {
        $errors['date'] = 'Date is required';
    }
    if (!$time) {
        $errors['time'] = 'Time is required';
    }
    if (!$color) {
        $errors['color'] = 'Color is required';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Save to database
    $result = saveRSVP([
        'date' => $date,
        'time' => $time,
        'color' => $color
    ]);
    
    // Store in session for current visit
    $_SESSION['picnic_rsvp'] = [
        'date' => $date,
        'time' => $time,
        'color' => $color,
        'food_suggestion' => getFoodSuggestion($color)
    ];
    
    echo json_encode($result);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Picnic Invite · Let's Go!</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- PAGE 1 - Invitation -->
    <section class="page active" id="page1">
        <img src="background.jpg" class="bg" alt="background">
        <img src="asking.png" class="girl" alt="cute girl character">
        
        <div class="overlay">
            <div class="center-box">
                <h1>Everyone... picnic tayo?</h1>
                <p class="sub">Be honest ah...</p>
                <div class="button-group">
                    <button class="btn-cute yes-btn">YES</button>
                    <button class="btn-cute no-btn" id="noBtn">NO</button>
                </div>
            </div>
        </div>
        
        <!-- Hidden reaction image -->
        <img src="pop.jpg" class="reaction-img" id="reactionImg" style="display: none;" alt="sad reaction">
    </section>

    <!-- PAGE 2 - Surprise reaction -->
    <section class="page" id="page2">
        <img src="background.jpg" class="bg" alt="background">
        <img src="happyy.png" class="girl" alt="surprised girl">
        
        <div class="overlay">
            <h1 class="left">WAIT- <br> Did you guys actually say yes??</h1>
            <p class="small-text">I was fully prepared to start begging. <br> Okay okay... this just got real.</p>
            <div class="text-center">
                <button class="next-btn" id="nextBtn">NEXT</button>
            </div>
        </div>
    </section>

    <!-- PAGE 3 - Date & Time picker -->
    <section class="page" id="page3">
        <img src="background.jpg" class="bg" alt="background">
        
        <div class="overlay">
            <h1 class="left">So... kailan kayo free?</h1>
            <form id="picnicForm">
                <label>Pick a date:</label>
                <input type="date" name="date" class="form-control" id="dateInput" required min="<?php echo date('Y-m-d'); ?>">
                
                <label>Pick a time:</label>
                <select name="time" class="form-select" id="timeSelect" required>
                    <option value="">Choose time</option>
                    <option value="1PM">1PM — for people who wake up before lunch (wow sana all responsible)</option>
                    <option value="2PM">2PM — enough time to fix yourself</option>
                    <option value="3PM">3PM — picnic but make it golden hour aesthetic</option>
                </select>
                
                <div class="text-center">
                    <button type="button" class="submit-btn" id="dateBtn">Set the date and time</button>
                </div>
            </form>
        </div>
    </section>

    <!-- PAGE 4 - Color picker -->
    <section class="page" id="page4">
        <img src="background.jpg" class="bg" alt="background">
        
        <div class="overlay text-center">
            <h1>Choose your color</h1>
            <p class="small-text">(your color decides the food you bring hehe)</p>
            
            <div class="colors">
                <div class="color-box red" data-color="Red"></div>
                <div class="color-box blue" data-color="Blue"></div>
                <div class="color-box green" data-color="Green"></div>
                <div class="color-box yellow" data-color="Yellow"></div>
                <div class="color-box purple" data-color="Purple"></div>
                <div class="color-box pink" data-color="Pink"></div>
                <div class="color-box black" data-color="Black"></div>
                <div class="color-box orange" data-color="Orange"></div>
            </div>
            
            <p id="colorError" class="error-msg" style="display: none;">Pili ka muna color para may ambag!😒</p>
            <button class="submit-btn" id="submitBtn">Submit</button>
        </div>
    </section>

    <!-- PAGE 5 - Final bubble message -->
    <section class="page" id="page5">
        <img src="background.jpg" class="bg" alt="background">
        <img src="excitee.png" class="girl final-girl" alt="excited girl">
        
        <div class="overlay bubble-overlay">
            <h1>YAYYYY SEE YAHH AT OUR HOUSE!!</h1>
            <p class="small-text">PS. Glad you guys didn't say no <br> Can't wait to laugh, eat, take blurry pictures, and make chika all day.</p>
            
            <div class="speech-bubble" id="speechBubble">
                See youuu!! <br>
                xoxo
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>