<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoneTask extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'done_date' => 'date'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
