<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneDetail extends Model
{
    use HasFactory;
    protected $table = 'zone_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'zona_code',
        'city_code',
        'created_by',
        'updated_by',
    ];
}
