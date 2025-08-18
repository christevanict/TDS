<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDebtCreditNote extends Model
{
    use HasFactory;

    protected $table = 'sales_debt_credit_note'; // Specify the correct table name

    protected $fillable = [
        'sales_credit_note_number',
        'sales_credit_note_date',
        'customer_code',
        'invoice_number',
        'total',
        'account_receivable',
        'company_code',
        'department_code',
        'status',
        'created_by',
        'updated_by'
    ];
    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_number', 'sales_invoice_number');
    }

    public function details()
    {
        return $this->hasMany(SalesDebtCreditNoteDetail::class, 'sales_credit_note_number', 'sales_credit_note_number');
    }
}
