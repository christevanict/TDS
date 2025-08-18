<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralJournal extends Model
{
    use HasFactory;
    protected $table = 'general_journal';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'general_journal_number',
        'general_journal_date',
        'nominal_debet',
        'nominal_credit',
        'note',
        'token',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];
    public function details() {
        return $this->hasMany(GeneralJournalDetail::class, 'general_journal_number', 'general_journal_number');
    }

    public function users(){
        return $this->belongsTo(Users::class, 'created_by', 'username');
    }
}
