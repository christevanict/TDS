<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    use HasFactory;
    protected $table = 'receivable';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'document_number',
        'document_date',
        'customer_code',
        'total_debt',
        'debt_balance',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
        'due_date',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code'); // assuming 'code' is the primary key in the Department model
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class,'document_number','sales_invoice_number');
    }
}
