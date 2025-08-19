<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivablePaymentDetail extends Model
{
    use HasFactory;
    protected $table = 'receivable_payment_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'receivable_payment_number',
        'receivable_payment_date',
        'receivable_payment_detail_id',
        'customer_code',
        'document_number',
        'document_date',
        'document_nominal',
        'document_payment',
        'discount',
        'nominal',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function paymentDetails(){
        return $this->hasMany(ReceivablePaymentDetailPay::class,'receivable_payment_detail_id','receivable_payment_detail_id');
    }

    public function receivables()
    {
        return $this->belongsTo(Receivable::class,'document_number','document_number');
    }
}
