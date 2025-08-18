<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $table = 'department';
    protected $softDelete = false;
      public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'company_code',
        'department_code',
        'department_name',
        'address',
        'phone',
        'created_by',
        'updated_by',
    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
    public function cashIns()
    {
        return $this->hasMany(BankCashIn::class, 'department_code', 'department_code');
    }
}
