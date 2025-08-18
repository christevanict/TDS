<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayablePaymentDetailPay extends Model
{
    use HasFactory;
    protected $table = 'payable_payment_detail_pay';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'payable_payment_number',
        'payable_payment_date',
        'payable_payment_detail_id',
        'payment_method',
        'payment_nominal',
        'bg_check_number',
        'acc_debt_bg',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function paymentDetail(){
        return $this->belongsTo(PayablePaymentDetail::class,'payable_payment_detail_id','payable_payment_detail_id');
    }
}
