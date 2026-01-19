<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticeReceiver extends Model
{
    use HasFactory;

    protected $table = 'notice_receivers';

    public $timestamps = false;

    protected $fillable = [
        'notice_id',
        'notice_receiver_id'
    ];

    public function notice_id()
    {
        return $this->belongsTo(Notice::class, 'notice_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'notice_receiver_id', 'id');
    }
}
