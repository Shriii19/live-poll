<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = ['poll_id', 'option_id', 'ip_address', 'is_released'];

    protected $casts = [
        'is_released' => 'boolean',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function option()
    {
        return $this->belongsTo(PollOption::class, 'option_id');
    }
}
