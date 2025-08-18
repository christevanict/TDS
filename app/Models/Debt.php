<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory;
    protected $table = 'debt';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'document_number',
        'document_date',
        'supplier_code',
        'total_debt',
        'debt_balance',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
        'due_date',
    ];

    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_code','supplier_code');
    }
}
