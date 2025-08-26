<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['sku', 'name', 'description', 'price', 'color', 'size', 'arm', 'image', 'status'];
}
