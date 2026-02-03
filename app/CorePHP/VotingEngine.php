<?php
/**
 * Core PHP Voting Engine
 * Handles: Voting rules, IP validation, Vote release & rollback logic
 * This is pure PHP logic as per the requirements
 */

namespace App\CorePHP;

class VotingEngine
{
    private $pdo;
    
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Validate IP address format
     * @param string $ip
     * @return bool
     */
    public function validateIP(string $ip): bool
    {
        // Check if valid IPv4 or IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return true;
        }
        return false;
    }

    /**
     * Get client IP address
     * @return string
     */
    public function getClientIP(): string
    {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can contain multiple IPs, get the first one
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Validate the IP before returning
        if ($this->validateIP($ip)) {
            return $ip;
        }
        
        return '0.0.0.0';
    }

    /**
     * Check if IP has already voted on a poll (and vote is not released)
     * @param int $pollId
     * @param string $ipAddress
     * @return bool
     */
    public function hasVoted(int $pollId, string $ipAddress): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as vote_count 
             FROM votes 
             WHERE poll_id = :poll_id 
             AND ip_address = :ip_address 
             AND is_released = 0"
        );
        
        $stmt->execute([
            ':poll_id' => $pollId,
            ':ip_address' => $ipAddress
        ]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['vote_count'] > 0;
    }

    /**
     * Get existing vote for an IP on a poll
     * @param int $pollId
     * @param string $ipAddress
     * @return array|null
     */
    public function getExistingVote(int $pollId, string $ipAddress): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT v.*, po.option_text 
             FROM votes v
             JOIN poll_options po ON v.option_id = po.id
             WHERE v.poll_id = :poll_id 
             AND v.ip_address = :ip_address 
             AND v.is_released = 0"
        );
        
        $stmt->execute([
            ':poll_id' => $pollId,
            ':ip_address' => $ipAddress
        ]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Cast a vote - Core voting logic
     * @param int $pollId
     * @param int $optionId
     * @param string $ipAddress
     * @return array ['success' => bool, 'message' => string]
     */
    public function castVote(int $pollId, int $optionId, string $ipAddress): array
    {
        // Validate IP
        if (!$this->validateIP($ipAddress)) {
            return [
                'success' => false,
                'message' => "We couldn't verify your network address. Please try again."
            ];
        }

        // Check if already voted
        if ($this->hasVoted($pollId, $ipAddress)) {
            return [
                'success' => false,
                'message' => 'It looks like you already voted on this poll. Thanks for participating!'
            ];
        }

        // Verify poll exists and is active
        $stmt = $this->pdo->prepare(
            "SELECT id, status FROM polls WHERE id = :poll_id"
        );
        $stmt->execute([':poll_id' => $pollId]);
        $poll = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$poll) {
            return [
                'success' => false,
                'message' => "We couldn't find that poll."
            ];
        }

        if ($poll['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'This poll is closed right now.'
            ];
        }

        // Verify option belongs to poll
        $stmt = $this->pdo->prepare(
            "SELECT id FROM poll_options WHERE id = :option_id AND poll_id = :poll_id"
        );
        $stmt->execute([
            ':option_id' => $optionId,
            ':poll_id' => $pollId
        ]);
        
        if (!$stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Please choose one of the available options.'
            ];
        }

        try {
            $this->pdo->beginTransaction();

            // Insert vote
            $stmt = $this->pdo->prepare(
                "INSERT INTO votes (poll_id, option_id, ip_address, is_released, created_at, updated_at) 
                 VALUES (:poll_id, :option_id, :ip_address, 0, NOW(), NOW())"
            );
            
            $stmt->execute([
                ':poll_id' => $pollId,
                ':option_id' => $optionId,
                ':ip_address' => $ipAddress
            ]);

            // Record in vote history
            $stmt = $this->pdo->prepare(
                "INSERT INTO vote_history (poll_id, option_id, ip_address, action, created_at, updated_at) 
                 VALUES (:poll_id, :option_id, :ip_address, 'voted', NOW(), NOW())"
            );
            
            $stmt->execute([
                ':poll_id' => $pollId,
                ':option_id' => $optionId,
                ':ip_address' => $ipAddress
            ]);

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Thanks! Your vote has been recorded.'
            ];

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'We ran into an issue saving your vote. Please try again.'
            ];
        }
    }

    /**
     * Release an IP's vote - Vote rollback logic
     * @param int $pollId
     * @param string $ipAddress
     * @return array ['success' => bool, 'message' => string]
     */
    public function releaseVote(int $pollId, string $ipAddress): array
    {
        // Get the current vote
        $existingVote = $this->getExistingVote($pollId, $ipAddress);
        
        if (!$existingVote) {
            return [
                'success' => false,
                'message' => "We couldn't find an active vote for that IP."
            ];
        }

        try {
            $this->pdo->beginTransaction();

            // Mark vote as released (soft delete - preserves history)
            $stmt = $this->pdo->prepare(
                "UPDATE votes 
                 SET is_released = 1, updated_at = NOW() 
                 WHERE poll_id = :poll_id 
                 AND ip_address = :ip_address 
                 AND is_released = 0"
            );
            
            $stmt->execute([
                ':poll_id' => $pollId,
                ':ip_address' => $ipAddress
            ]);

            // Record release in vote history
            $stmt = $this->pdo->prepare(
                "INSERT INTO vote_history (poll_id, option_id, ip_address, action, created_at, updated_at) 
                 VALUES (:poll_id, :option_id, :ip_address, 'released', NOW(), NOW())"
            );
            
            $stmt->execute([
                ':poll_id' => $pollId,
                ':option_id' => $existingVote['option_id'],
                ':ip_address' => $ipAddress
            ]);

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Vote released. This IP can vote again.'
            ];

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'We ran into an issue releasing that vote. Please try again.'
            ];
        }
    }

    /**
     * Get vote history for an IP on a poll
     * @param int $pollId
     * @param string $ipAddress
     * @return array
     */
    public function getVoteHistory(int $pollId, string $ipAddress): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT vh.*, po.option_text 
             FROM vote_history vh
             LEFT JOIN poll_options po ON vh.option_id = po.id
             WHERE vh.poll_id = :poll_id 
             AND vh.ip_address = :ip_address
             ORDER BY vh.created_at ASC"
        );
        
        $stmt->execute([
            ':poll_id' => $pollId,
            ':ip_address' => $ipAddress
        ]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all voters for a poll (for admin)
     * @param int $pollId
     * @return array
     */
    public function getPollVoters(int $pollId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT v.ip_address, v.is_released, v.created_at as vote_time, 
                    po.option_text,
                    (SELECT COUNT(*) FROM vote_history vh 
                     WHERE vh.poll_id = v.poll_id AND vh.ip_address = v.ip_address) as history_count
             FROM votes v
             JOIN poll_options po ON v.option_id = po.id
             WHERE v.poll_id = :poll_id
             ORDER BY v.created_at DESC"
        );
        
        $stmt->execute([':poll_id' => $pollId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get poll results - vote count per option
     * @param int $pollId
     * @return array
     */
    public function getPollResults(int $pollId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT po.id, po.option_text, 
                    COUNT(CASE WHEN v.is_released = 0 THEN 1 END) as vote_count
             FROM poll_options po
             LEFT JOIN votes v ON po.id = v.option_id
             WHERE po.poll_id = :poll_id
             GROUP BY po.id, po.option_text
             ORDER BY po.id"
        );
        
        $stmt->execute([':poll_id' => $pollId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get total active votes for a poll
     * @param int $pollId
     * @return int
     */
    public function getTotalVotes(int $pollId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as total FROM votes 
             WHERE poll_id = :poll_id AND is_released = 0"
        );
        
        $stmt->execute([':poll_id' => $pollId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return (int) $result['total'];
    }
}
