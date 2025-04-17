<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabTestBooking extends Model
{
    protected $table = 'tblLabTestBookings';

    protected $primaryKey = 'BookingID';

    protected $fillable = [
        'UserID', 'Status',
    ];

    public function details()
    {
        return $this->hasMany(LabTestBookingDetail::class, 'BookingID', 'BookingID');
    }
}
