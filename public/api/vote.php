<?php
/**
 * Vote API Handler
 * Uses Core PHP VotingEngine
 */
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please sign in before voting.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'This endpoint only accepts votes via POST.'], 405);
}

$pollId = $_POST['poll_id'] ?? null;
$optionId = $_POST['option_id'] ?? null;

if (!$pollId || !$optionId) {
    jsonResponse(['success' => false, 'message' => 'Please choose a poll and an option before voting.'], 400);
}

$pdo = getDB();
$engine = new \App\CorePHP\VotingEngine($pdo);
$clientIP = getClientIP();

// Use Core PHP VotingEngine for voting logic
$result = $engine->castVote((int)$pollId, (int)$optionId, $clientIP);

jsonResponse($result, $result['success'] ? 200 : 400);
