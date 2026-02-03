<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoteHistory extends Model
{
    protected $table = 'vote_history';
    
    protected $fillable = ['poll_id', 'option_id', 'ip_address', 'action'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function option()
    {
        return $this->belongsTo(PollOption::class, 'option_id');
    }
}
