<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWeight extends Model
{
    use HasFactory;
    protected $table = 'user_weights';
     protected $fillable = [
        'user_id',
        'date',
        'time',
        'weight',
        'comments',
    ];
}
