<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'promo_type',
        'value',
        'item_limit',
        'start_date',
        'end_date',
        'status',
    ];

    protected $dates = ['start_date', 'end_date'];
}
