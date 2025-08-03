<?php

namespace App\Models;

use App\Models\OrderItems;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'image_url'
    ];

    // Declerate The ORM Method

    public function orderItems() {
        return $this->hasMany(OrderItems::class);
    }
    protected $table = 'products'; // Pastikan nama table sesuai
    
    // Relasi ke categories
    public function categories()
    {
        return $this->belongsTo(Categories::class, 'category_id'); // Sesuaikan foreign key jika perlu
    }
}
