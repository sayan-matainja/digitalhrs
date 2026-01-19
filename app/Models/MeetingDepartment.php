<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingDepartment extends Model
{
    use HasFactory;

    protected $table = 'meeting_departments';

    public $timestamps = false;

    protected $fillable = [
        'team_meeting_id',
        'department_id'
    ];

    public function teamMeeting()
    {
        return $this->belongsTo(TeamMeeting::class, 'team_meeting_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
