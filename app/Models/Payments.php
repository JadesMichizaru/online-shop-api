<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model {
    protected $fillable = [
        'order_id',
        'payment_method',
        'account_number',
        'account_name',
        'amount',
        'status',
        'proof_url'
    ];

    public function orderItems() {
        return $this->belongsTo(OrderItems::class, 'order_id');
    }
}
