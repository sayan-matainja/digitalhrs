<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TadaAttachment extends Model
{
    use HasFactory;

    protected $table = 'tada_attachments';

    protected $fillable = [
        'tada_id',
        'attachment'
    ];

    const ATTACHMENT_UPLOAD_PATH = 'uploads/tada/attachment/';

    public function tada()
    {
        return $this->belongsTo(Tada::class, 'tada_id', 'id');
    }

}
