<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'tblOrders';

    protected $fillable = ['UserID', 'TotalPrice', 'Status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'OrderID');
    }
}
