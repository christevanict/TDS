<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralJournalDetail extends Model
{
    use HasFactory;
    protected $table = 'general_journal_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'general_journal_number',
        'account_number',
        'nominal_debet',
        'nominal_credit',
        'note',
        'row_number',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function coa(){
        return $this->belongsTo(CoA::class, 'account_number', 'account_number');
    }
}
