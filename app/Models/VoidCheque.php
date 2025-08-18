<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoidCheque extends Model
{
    use HasFactory;
    protected $table = 'void_cheque';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'document_number',
        'document_date',
        'account_number',
        'nominal',
        'note',
        'company_code',
        'department_code',
    ];
}
