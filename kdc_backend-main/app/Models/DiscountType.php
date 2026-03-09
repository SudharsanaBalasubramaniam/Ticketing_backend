<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountType extends Model
{
    use HasFactory;
    protected $table = 'discount_type';
    protected $primarykey = 'id';

    protected $fillable = [
        'name',
        'value',
        'is_active',
        'discount_type'
    ];
    protected $casts = [
        'is_active' => 'boolean'
    ];
}
