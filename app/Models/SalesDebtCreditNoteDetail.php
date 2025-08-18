<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDebtCreditNoteDetail extends Model
{
    use HasFactory;

    protected $table = 'sales_debt_credit_note_details';

    protected $fillable = [
        'sales_credit_note_number',
        'account_number',
        'nominal',
        'note',
        'created_by',
        'updated_by',
    ];

}
