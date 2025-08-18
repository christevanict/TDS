<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'supplier';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'supplier_code',
        'supplier_name',
        'address',
        'warehouse_address',
        'phone_number',
        'department_code',
        'pkp',
        'include',
        'currency_code',
        'company_code',
        'account_payable',
        'account_dp',
        'account_payable_grpo',
        'account_add_tax',
        'department_code',
        'npwp',
        'created_by',
        'updated_by',
    ];

    public function currency(){
        return $this->belongsTo(Currency::class,'currency_code','currency_code');
    }
    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }

    public function debts(){
        return $this->hasMany(Debt::class,'supplier_code','supplier_code');
    }
}
