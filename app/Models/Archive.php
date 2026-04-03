<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $fillable = [
        'filename',
        'type',
        'period_label',
        'total_records',
    ];
}
