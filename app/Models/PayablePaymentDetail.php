<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayablePaymentDetail extends Model
{
    use HasFactory;
    protected $table = 'payable_payment_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'payable_payment_number',
        'payable_payment_date',
        'payable_payment_detail_id',
        'supplier_code',
        'document_number',
        'document_date',
        'document_nominal',
        'document_payment',
        'nominal_payment',
        'discount',
        'balance',
        'acc_debt',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function paymentDetails(){
        return $this->hasMany(PayablePaymentDetailPay::class,'payable_payment_detail_id','payable_payment_detail_id');
    }
}
