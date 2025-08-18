<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClearingChequeDetail extends Model
{
    use HasFactory;
    protected $table = 'clearing_cheque_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'document_number',
        'document_payment_number',
        'document_payment_date',
        'bg_cheque_number',
        'nominal',
        'note',
        'row_number',
        'company_code',
        'department_code',
    ];
}
