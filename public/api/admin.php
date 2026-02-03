<?php
/**
 * Admin API Handler
 * Uses Core PHP VotingEngine for IP release and vote rollback
 */
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please sign in to access admin tools.'], 401);
}

if (!isAdmin()) {
    jsonResponse(['success' => false, 'message' => 'Admin access is required for this action.'], 403);
}

$pdo = getDB();
$engine = new \App\CorePHP\VotingEngine($pdo);
$action = $_GET['action'] ?? '';

// GET /api/admin/polls - Get all polls with vote counts
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action)) {
    $stmt = $pdo->query("
        SELECT p.*, 
               (SELECT COUNT(*) FROM votes v WHERE v.poll_id = p.id AND v.is_released = 0) as active_votes_count
        FROM polls p 
        ORDER BY p.created_at DESC
    ");
    $polls = $stmt->fetchAll();
    
    foreach ($polls as &$poll) {
        $stmt = $pdo->prepare("SELECT * FROM poll_options WHERE poll_id = ?");
        $stmt->execute([$poll['id']]);
        $poll['options'] = $stmt->fetchAll();
    }
    
    jsonResponse(['success' => true, 'polls' => $polls]);
}

// GET /api/admin/polls/{id}/voters
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'voters') {
    $pollId = $_GET['poll_id'];
    $voters = $engine->getPollVoters($pollId);
    
    $stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
    $stmt->execute([$pollId]);
    $poll = $stmt->fetch();
    
    jsonResponse(['success' => true, 'poll' => $poll, 'voters' => $voters]);
}

// GET /api/admin/polls/{id}/voters-history - Get all voters with full history
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'voters-history') {
    $pollId = $_GET['poll_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
    $stmt->execute([$pollId]);
    $poll = $stmt->fetch();
    
    if (!$poll) {
        jsonResponse(['success' => false, 'message' => "We couldn't find that poll."], 404);
    }
    
    // Get unique IPs from vote history
    $stmt = $pdo->prepare("SELECT DISTINCT ip_address FROM vote_history WHERE poll_id = ?");
    $stmt->execute([$pollId]);
    $ips = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $votersWithHistory = [];
    foreach ($ips as $ip) {
        $history = $engine->getVoteHistory($pollId, $ip);
        $currentVote = $engine->getExistingVote($pollId, $ip);
        
        $votersWithHistory[] = [
            'ip_address' => $ip,
            'current_vote' => $currentVote,
            'history' => $history,
            'can_release' => $currentVote !== null
        ];
    }
    
    jsonResponse(['success' => true, 'poll' => $poll, 'voters' => $votersWithHistory]);
}

// GET /api/admin/polls/{id}/history/{ip} - Get vote history for specific IP
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'history') {
    $pollId = $_GET['poll_id'];
    $ip = urldecode($_GET['ip']);
    
    $history = $engine->getVoteHistory($pollId, $ip);
    jsonResponse(['success' => true, 'history' => $history]);
}

// POST /api/admin/release-ip - Release IP vote (uses Core PHP VotingEngine)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pollId = $_POST['poll_id'] ?? null;
    $ipAddress = $_POST['ip_address'] ?? null;
    
    if (!$pollId || !$ipAddress) {
        jsonResponse(['success' => false, 'message' => 'Please provide a poll and an IP address.'], 400);
    }
    
    // Use Core PHP VotingEngine for vote release/rollback
    $result = $engine->releaseVote((int)$pollId, $ipAddress);
    
    jsonResponse($result, $result['success'] ? 200 : 400);
}
