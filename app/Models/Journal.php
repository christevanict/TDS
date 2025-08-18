<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;
    protected $table = 'journal';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'document_number',
        'document_date',
        'account_number',
        'notes',
        'debet_nominal',
        'credit_nominal',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];
    public function coas(){
        return $this->belongsTo(Coa::class,'account_number','account_number');
    }
}
