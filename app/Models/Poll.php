<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = ['question', 'status'];

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function activeVotes()
    {
        return $this->hasMany(Vote::class)->where('is_released', false);
    }

    public function voteHistory()
    {
        return $this->hasMany(VoteHistory::class);
    }
}
