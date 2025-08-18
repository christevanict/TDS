<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    use HasFactory;
    protected $table = 'item_unit';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'unit',
        'unit_name',
        'company_code',
        'created_by',
        'updated_by',
    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
}
