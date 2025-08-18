<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDebtCreditNote extends Model
{
    use HasFactory;

    protected $table = 'purchase_debt_credit_note'; // Specify the correct table name

    protected $fillable = [
        'purchase_credit_note_number',
        'purchase_credit_note_date',
        'supplier_code',
        'invoice_number',
        'total',
        'account_payable',
        'company_code',
        'department_code',
        'status',
        'created_by',
        'updated_by'
    ];
    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'invoice_number', 'purchase_invoice_number');
    }

    public function details()
    {
        return $this->hasMany(PurchaseDebtCreditNoteDetail::class, 'purchase_credit_note_number', 'purchase_credit_note_number');
    }
}
