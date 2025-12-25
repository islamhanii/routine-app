<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function doneTasks()
    {
        return $this->hasMany(DoneTask::class);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public static function rules()
    {
        return [
            'title' => 'required|string|max:255'
        ];
    }
}
