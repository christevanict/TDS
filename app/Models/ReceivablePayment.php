<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivablePayment extends Model
{
    use HasFactory;
    protected $table = 'receivable_payment';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
    'receivable_payment_number',
    'receivable_payment_date',
    'customer_code',
    'total_debt',
    'acc_total',
    'acc_disc',
    'acc_total_disc',
    'token',
    'company_code',
    'department_code',
    'created_by',
    'updated_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_code','customer_code');
    }

    public function details(){
        return $this->hasMany(ReceivablePaymentDetail::class,'receivable_payment_number','receivable_payment_number');
    }
}
