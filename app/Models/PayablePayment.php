<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayablePayment extends Model
{
    use HasFactory;
    protected $table = 'payable_payment';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'payable_payment_number',
        'payable_payment_date',
        'supplier_code',
        'total_debt',
        'acc_total',
        'acc_disc',
        'token',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_code','supplier_code');
    }


    public function details(){
        return $this->hasMany(PayablePaymentDetail::class,'payable_payment_number','payable_payment_number');
    }
}
