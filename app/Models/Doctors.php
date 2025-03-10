<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctors extends Model
{
    use HasFactory;
    public function department()
    {
        return $this->hasOne(Departments::class, 'id', 'department_id');
    }
}
