<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\CorePHP\VotingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    private function getVotingEngine(): VotingEngine
    {
        $pdo = DB::connection()->getPdo();
        return new VotingEngine($pdo);
    }

    public function index()
    {
        return view('polls.index');
    }

    public function getPolls()
    {
        $polls = Poll::where('status', 'active')
            ->with('options')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'polls' => $polls
        ]);
    }

    public function getPoll($id)
    {
        $poll = Poll::with('options')->find($id);
        
        if (!$poll) {
            return response()->json([
                'success' => false,
                'message' => 'Poll not found'
            ], 404);
        }

        $engine = $this->getVotingEngine();
        $clientIP = $engine->getClientIP();
        $hasVoted = $engine->hasVoted($id, $clientIP);
        $existingVote = $hasVoted ? $engine->getExistingVote($id, $clientIP) : null;

        return response()->json([
            'success' => true,
            'poll' => $poll,
            'has_voted' => $hasVoted,
            'voted_option' => $existingVote ? $existingVote['option_id'] : null
        ]);
    }

    public function vote(Request $request)
    {
        $request->validate([
            'poll_id' => 'required|integer|exists:polls,id',
            'option_id' => 'required|integer|exists:poll_options,id'
        ]);

        $isValidOptionForPoll = PollOption::where('id', $request->option_id)
            ->where('poll_id', $request->poll_id)
            ->exists();

        if (!$isValidOptionForPoll) {
            return response()->json([
                'success' => false,
                'message' => 'Selected option does not belong to this poll.'
            ], 422);
        }

        $engine = $this->getVotingEngine();
        $clientIP = $engine->getClientIP();

        $result = $engine->castVote(
            $request->poll_id,
            $request->option_id,
            $clientIP
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function getResults($id)
    {
        $poll = Poll::find($id);

        if (!$poll) {
            return response()->json([
                'success' => false,
                'message' => 'Poll not found'
            ], 404);
        }

        $engine = $this->getVotingEngine();
        $results = $engine->getPollResults($id);
        $totalVotes = $engine->getTotalVotes($id);

        return response()->json([
            'success' => true,
            'results' => $results,
            'total_votes' => $totalVotes
        ]);
    }

    public function create()
    {
        return view('polls.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255'
        ]);

        $question = trim($request->question);
        $options = collect($request->options)
            ->map(fn ($option) => trim($option))
            ->filter(fn ($option) => $option !== '')
            ->unique()
            ->values();

        if ($options->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide at least two unique non-empty options.'
            ], 422);
        }

        $poll = Poll::create([
            'question' => $question,
            'status' => 'active'
        ]);

        foreach ($options as $optionText) {
            PollOption::create([
                'poll_id' => $poll->id,
                'option_text' => $optionText
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Poll created successfully',
            'poll' => $poll->load('options')
        ]);
    }

    public function toggleStatus($id)
    {
        $poll = Poll::find($id);
        
        if (!$poll) {
            return response()->json([
                'success' => false,
                'message' => 'Poll not found'
            ], 404);
        }

        $poll->status = $poll->status === 'active' ? 'inactive' : 'active';
        $poll->save();

        return response()->json([
            'success' => true,
            'message' => 'Poll status updated',
            'status' => $poll->status
        ]);
    }
}
