<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\Vote;
use App\Models\VoteHistory;
use App\CorePHP\VotingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    private function getVotingEngine(): VotingEngine
    {
        $pdo = DB::connection()->getPdo();
        return new VotingEngine($pdo);
    }

    public function dashboard()
    {
        if (!Auth::user()->is_admin) {
            return redirect('/polls');
        }
        return view('admin.dashboard');
    }

    public function getPolls()
    {
        $polls = Poll::with('options')
            ->withCount(['votes as active_votes_count' => function($query) {
                $query->where('is_released', false);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'polls' => $polls
        ]);
    }

    public function getPollVoters($pollId)
    {
        $poll = Poll::with('options')->find($pollId);
        
        if (!$poll) {
            return response()->json([
                'success' => false,
                'message' => 'Poll not found'
            ], 404);
        }

        $engine = $this->getVotingEngine();
        $voters = $engine->getPollVoters($pollId);

        return response()->json([
            'success' => true,
            'poll' => $poll,
            'voters' => $voters
        ]);
    }

    public function getVoteHistory($pollId, $ip)
    {
        $engine = $this->getVotingEngine();
        $history = $engine->getVoteHistory($pollId, $ip);

        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    public function releaseIP(Request $request)
    {
        $request->validate([
            'poll_id' => 'required|integer',
            'ip_address' => 'required|string'
        ]);

        $engine = $this->getVotingEngine();
        $result = $engine->releaseVote($request->poll_id, $request->ip_address);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function getAllVotersWithHistory($pollId)
    {
        $poll = Poll::with('options')->find($pollId);
        
        if (!$poll) {
            return response()->json([
                'success' => false,
                'message' => 'Poll not found'
            ], 404);
        }

        // Get unique IPs that have ever voted
        $voterIPs = VoteHistory::where('poll_id', $pollId)
            ->select('ip_address')
            ->distinct()
            ->pluck('ip_address');

        $engine = $this->getVotingEngine();
        $votersWithHistory = [];

        foreach ($voterIPs as $ip) {
            $history = $engine->getVoteHistory($pollId, $ip);
            $currentVote = $engine->getExistingVote($pollId, $ip);
            
            $votersWithHistory[] = [
                'ip_address' => $ip,
                'current_vote' => $currentVote,
                'history' => $history,
                'can_release' => $currentVote !== null
            ];
        }

        return response()->json([
            'success' => true,
            'poll' => $poll,
            'voters' => $votersWithHistory
        ]);
    }
}
