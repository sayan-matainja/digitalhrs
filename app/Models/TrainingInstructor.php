<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingInstructor extends Model
{
    use HasFactory;
    protected $table = 'training_instructors';

    public $timestamps = false;

    protected $fillable = [
        'trainer_type',
        'training_id',
        'trainer_id',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id', 'id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id', 'id');
    }
}
