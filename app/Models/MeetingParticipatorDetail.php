<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingParticipatorDetail extends Model
{
    use HasFactory;

    protected $table = 'team_meeting_members';

    public $timestamps = false;

    protected $fillable = [
        'team_meeting_id',
        'meeting_participator_id'
    ];

    public function notice_id()
    {
        return $this->belongsTo(TeamMeeting::class, 'team_meeting_id', 'id');
    }

    public function participator()
    {
        return $this->belongsTo(User::class, 'meeting_participator_id', 'id');
    }
}
