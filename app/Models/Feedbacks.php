<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedbacks extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_name',
        'phone',
        'consultation_date',
        'type',
        'doctor_name',
        'service_area',
        'age_group',
        'gender',
        'visit_purpose',
        'treatment_outcome',
        'additional_comments',
        'follow_up_permission',
        'overall_satisfaction',
        'overall_satisfaction',
        'consultation_rating',
        'quality_of_facilities',
        'staff_behavior',
        'empathy_and_respect'
    ];
}
