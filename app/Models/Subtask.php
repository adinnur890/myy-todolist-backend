<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    protected $fillable = ['todo_id', 'title', 'description', 'status'];

    public function todo()
    {
        return $this->belongsTo(Todo::class);
    }
}
