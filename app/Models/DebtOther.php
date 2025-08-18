<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtOther extends Model
{
    use HasFactory;
    protected $table = 'debt_other';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'debt_other_number',
        'document_date',
        'due_date',
        'token',
        'supplier_code',
        'purchase_invoice_number',
        'notes',
        'department_code',
        'company_code',
        'created_by',
        'updated_by',
    ];

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class,'supplier_code','supplier_code');
    }

    public function details()
    {
        return $this->hasMany(DebtOtherDetail::class,'debt_other_number','debt_other_number');
    }

    
}
