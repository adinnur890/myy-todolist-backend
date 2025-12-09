<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['name', 'description', 'price', 'duration_days'];

    protected $appends = ['duration_months'];

    public function getDurationMonthsAttribute()
    {
        return round($this->duration_days / 30);
    }
}
