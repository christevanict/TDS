<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customer';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'customer_code',
        'customer_name',
        'address',
        'warehouse_address',
        'phone_number',
        'pkp',
        'include',
        'bonded_zone',
        'currency_code',
        'category_customer',
        'group_customer',
        'zone',
        'city',
        'sales',
        'npwp',
        'email',
        'nik',
        'is_mbe2',
        'is_mbe3',
        'is_mbe4',
        'account_receivable',
        'account_dp',
        'account_add_tax',
        'account_add_tax_bonded_zone',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
    public function currency(){
        return $this->belongsTo(Currency::class,'currency_code','currency_code');
    }
    public function group_customers(){
        return $this->belongsTo(GroupCustomer::class,'code_group','group_customer');
    }
    public function category_customers(){
        return $this->belongsTo(CategoryCustomer::class,'category_code','category_customer');
    }

    public function receivables(){
        return $this->hasMany(Receivable::class,'customer_code','customer_code');
    }

    public function hasGroup()
    {
        return $this->belongsTo(GroupCustomer::class,'customer_code','code_group');
    }



}
