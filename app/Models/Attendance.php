<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'date',
        'time',
        'type',
        'status',
        'method',
    ];

    /**
     * Get the student for the attendance.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
