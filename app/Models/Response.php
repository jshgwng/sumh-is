<?php

namespace App\Models;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Response extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'response_id';
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'response_id',
        'survey_id',
        'user_id',
        'response',
        'is_anonymous',
    ];

    public function survey(){
        return $this->belongsTo(Survey::class, 'survey_id', 'survey_id');
    }
    
}
