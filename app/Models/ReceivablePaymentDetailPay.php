<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivablePaymentDetailPay extends Model
{
    use HasFactory;
    protected $table = 'receivable_payment_detail_pay';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'receivable_payment_number',
        'receivable_payment_date',
        'receivable_payment_detail_id',
        'payment_method',
        'payment_nominal',
        'bg_check_number',
        'acc_debt_bg',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];
}
