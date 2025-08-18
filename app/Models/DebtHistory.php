<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtHistory extends Model
{
    use HasFactory;
    protected $table = 'debt_history';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'document_number',
        'document_date',
        'supplier_code',
        'payment_number',
        'payment_method',
        'payment_date',
        'total_debt',
        'payment',
        'debt_balance',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];
}
