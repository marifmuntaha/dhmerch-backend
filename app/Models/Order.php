<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'whatsappId',
        'code',
        'name',
        'address',
        'phone',
        'productId',
        'type',
        'size',
        'arm',
        'price',
        'status',
        'payment',
        'reference',
        'payCode',
    ];
}
