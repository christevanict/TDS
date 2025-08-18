<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $table = 'payment_method';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'payment_method_code',
        'payment_name',
        'cost_payment',
        'account_number',
        'acc_number_cost',
        'company_code',
        'created_by',
        'updated_by',
    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
}
