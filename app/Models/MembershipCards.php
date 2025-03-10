<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipCards extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'cnic',
        'dob',
        'phone',
        'email',
        'membership_type',
        'address',
        'no_of_members',
        'preferred_hospital',
        'emergency_name',
        'emergency_contact',
    ];
}
