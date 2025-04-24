<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestReport extends Model
{
    use HasFactory;
    protected $table = 'lab_test_reports';

    protected $fillable = ['user_id', 'testname', 'report'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
