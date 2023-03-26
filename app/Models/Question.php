<?php

namespace App\Models;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'question_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'question_id',
        'survey_id',
        'question',
        'description',
        'type',
        'options',
        'is_required',
        'order',
    ];

    public function survey(){
        return $this->belongsTo(Survey::class, 'survey_id', 'survey_id');
    }
}
