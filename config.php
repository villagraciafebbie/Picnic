<?php
// config.php - Database configuration file
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'picnic_invite');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create connection function
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        // Log error but don't expose details to user
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Function to save RSVP to database
function saveRSVP($data) {
    $pdo = getDBConnection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO rsvp_responses (response_date, response_time, selected_color, ip_address, user_agent) 
            VALUES (:date, :time, :color, :ip, :user_agent)
        ");
        
        $stmt->execute([
            ':date' => $data['date'],
            ':time' => $data['time'],
            ':color' => $data['color'],
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        $id = $pdo->lastInsertId();
        
        return [
            'success' => true, 
            'message' => 'RSVP saved successfully!',
            'id' => $id
        ];
    } catch(PDOException $e) {
        error_log("Failed to save RSVP: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save RSVP'];
    }
}

// Function to get food suggestion based on color
function getFoodSuggestion($color) {
    $pdo = getDBConnection();
    if (!$pdo) {
        return "Something delicious! 🍽️";
    }
    
    try {
        $stmt = $pdo->prepare("SELECT food_item, description FROM color_food_mapping WHERE color_name = :color");
        $stmt->execute([':color' => $color]);
        $result = $stmt->fetch();
        
        if ($result) {
            return $result['food_item'] . " - " . $result['description'];
        }
        return "Something delicious! 🍽️";
    } catch(PDOException $e) {
        return "Something delicious! 🍽️";
    }
}

// Function to get statistics (for admin page)
function getStats() {
    $pdo = getDBConnection();
    if (!$pdo) {
        return null;
    }
    
    try {
        // Total responses
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM rsvp_responses");
        $total = $stmt->fetch()['total'];
        
        // Color popularity
        $stmt = $pdo->query("
            SELECT selected_color, COUNT(*) as count 
            FROM rsvp_responses 
            GROUP BY selected_color 
            ORDER BY count DESC
        ");
        $colorStats = $stmt->fetchAll();
        
        // Recent responses
        $stmt = $pdo->query("
            SELECT * FROM rsvp_responses 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $recent = $stmt->fetchAll();
        
        return [
            'total' => $total,
            'colorStats' => $colorStats,
            'recent' => $recent
        ];
    } catch(PDOException $e) {
        error_log("Failed to get stats: " . $e->getMessage());
        return null;
    }
}
?>