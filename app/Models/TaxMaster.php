<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxMaster extends Model
{
    use HasFactory;
    protected $table = 'tax_master';
    protected $softDelete = false;
      public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'tax_code',
        'tax_name',
        'tariff',
        'tax_base',
        'account_number',
        'company_code',
        'created_by',
        'updated_by',
    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
}
