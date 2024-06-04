<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'survey_details';

    protected $primaryKey = 'survey_id';

    protected $keyType = 'uuid';

    public $incrementing = false;

    protected $fillable = [
        'survey_name',
        'survey_description',
        'survey_slug',
        'survey_status',
        'survey_type',
        'survey_category',
        'survey_start_date',
        'survey_end_date',
        'survey_owner',
    ];
}