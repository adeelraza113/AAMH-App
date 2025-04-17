<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'tblOrderItems';

    protected $fillable = ['OrderID', 'ProductID', 'Qty'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID');
    }
    public $timestamps = false; 
}
