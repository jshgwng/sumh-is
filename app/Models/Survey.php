<?php

namespace App\Models;

use App\Models\User;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'survey_id';
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'survey_id',
        'title',
        'slug',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'is_public',
        'is_anonymous',
        'image_url',
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function questions(){
        return $this->hasMany(Question::class, 'survey_id', 'survey_id');
    }

    public function responses(){
        return $this->hasMany(Response::class, 'survey_id', 'survey_id');
    }
}
