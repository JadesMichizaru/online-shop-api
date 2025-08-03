<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    protected $fillable = [
        'user_id', 
        'product_id', 
        'quantity', 
        'price'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
