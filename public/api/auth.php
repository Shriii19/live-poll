<?php
/**
 * Authentication API Handler
 */
require_once __DIR__ . '/../../config.php';

$action = $_SERVER['REQUEST_URI'];

// Handle logout
if (strpos($action, '/logout') !== false) {
    session_destroy();
    header('Location: /login');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Please enter your email and password.'], 400);
    }
    
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            
            jsonResponse([
                'success' => true,
                'redirect' => '/polls',
                'is_admin' => (bool)$user['is_admin']
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => "That email or password doesn't look right."], 401);
        }
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Something went wrong on our side. Please try again.'], 500);
    }
}
