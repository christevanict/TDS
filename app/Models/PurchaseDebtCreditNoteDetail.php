<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDebtCreditNoteDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_debt_credit_note_details';

    protected $fillable = [
        'purchase_credit_note_number',
        'account_number',
        'nominal',
        'note',
        'created_by',
        'updated_by',
    ];

}
