<?php
/**
 * Polls API Handler
 */
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please sign in to view polls.'], 401);
}

$pdo = getDB();
$action = $_GET['action'] ?? '';

// GET /api/polls - List all active polls
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action)) {
    $stmt = $pdo->query("SELECT * FROM polls WHERE status = 'active' ORDER BY created_at DESC");
    $polls = $stmt->fetchAll();
    
    // Get options for each poll
    foreach ($polls as &$poll) {
        $stmt = $pdo->prepare("SELECT * FROM poll_options WHERE poll_id = ?");
        $stmt->execute([$poll['id']]);
        $poll['options'] = $stmt->fetchAll();
    }
    
    jsonResponse(['success' => true, 'polls' => $polls]);
}

// GET /api/polls/{id} - Get single poll
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'Poll ID is required.'], 400);
    }
    
    $stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
    $stmt->execute([$id]);
    $poll = $stmt->fetch();
    
    if (!$poll) {
        jsonResponse(['success' => false, 'message' => "We couldn't find that poll."], 404);
    }
    
    $stmt = $pdo->prepare("SELECT * FROM poll_options WHERE poll_id = ?");
    $stmt->execute([$id]);
    $poll['options'] = $stmt->fetchAll();
    
    // Check if user has voted
    $engine = new \App\CorePHP\VotingEngine($pdo);
    $clientIP = getClientIP();
    $hasVoted = $engine->hasVoted((int)$id, $clientIP);
    $existingVote = $hasVoted ? $engine->getExistingVote((int)$id, $clientIP) : null;
    
    jsonResponse([
        'success' => true,
        'poll' => $poll,
        'has_voted' => $hasVoted,
        'voted_option' => $existingVote ? $existingVote['option_id'] : null
    ]);
}

// GET /api/polls/{id}/results - Get poll results
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'results') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'Poll ID is required.'], 400);
    }
    
    $engine = new \App\CorePHP\VotingEngine($pdo);
    $results = $engine->getPollResults((int)$id);
    $totalVotes = $engine->getTotalVotes((int)$id);
    
    jsonResponse([
        'success' => true,
        'results' => $results,
        'total_votes' => $totalVotes
    ]);
}

// POST /api/polls - Create new poll (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['action'])) {
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Admin access is required for this action.'], 403);
    }
    
    $question = $_POST['question'] ?? '';
    $options = $_POST['options'] ?? [];
    $cleanOptions = array_values(array_filter(array_map('trim', (array)$options), function ($option) {
        return $option !== '';
    }));
    
    if (empty(trim($question)) || count($cleanOptions) < 2) {
        jsonResponse(['success' => false, 'message' => 'Please provide a question and at least two options.'], 400);
    }
    
    $stmt = $pdo->prepare("INSERT INTO polls (question, status, created_at, updated_at) VALUES (?, 'active', NOW(), NOW())");
    $stmt->execute([trim($question)]);
    $pollId = $pdo->lastInsertId();
    
    foreach ($cleanOptions as $optionText) {
        $stmt = $pdo->prepare("INSERT INTO poll_options (poll_id, option_text, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute([$pollId, $optionText]);
    }
    
    jsonResponse(['success' => true, 'message' => 'Your poll is ready and live!', 'poll_id' => $pollId]);
}

// POST /api/polls/{id}/toggle - Toggle poll status (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Admin access is required for this action.'], 403);
    }
    
    $id = $_POST['id'];
    
    $stmt = $pdo->prepare("UPDATE polls SET status = IF(status = 'active', 'inactive', 'active'), updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    
    jsonResponse(['success' => true, 'message' => 'Poll status updated successfully.']);
}
