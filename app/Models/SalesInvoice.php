<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    use HasFactory;
    protected $table = 'sales_invoice';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'sales_invoice_number',
        'sales_order_number',
        'document_date',
        'customer_code',
        'manual_number',
        'tax',
        'include',
        'subtotal',
        'disc_percent',
        'disc_nominal',
        'tax_revenue',
        'add_tax',
        'total',
        'contract_number',
        'notes',
        'status',
        'tax_revenue_tariff',
        'company_code',
        'reason',
        'is_nt',
        'token',
        'department_code',
        'acc_number_acc_receivable',
        'acc_number_acc_add_tax',
        'acc_number_acc_income_tax',
        'created_by',
        'updated_by',
        'due_date',
        'recap',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code'); // assuming 'code' is the primary key in the Company model
    }
    public function users()
    {
        return $this->belongsTo(Users::class, 'created_by', 'username'); // assuming 'code' is the primary key in the Company model
    }
    public function sos()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_number', 'sales_order_number'); // assuming 'code' is the primary key in the Company model
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code'); // assuming 'code' is the primary key in the Department model
    }
    public function details()
    {
        return $this->hasMany(SalesInvoiceDetail::class, 'sales_invoice_number', 'sales_invoice_number');
    }
    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }
    public function taxs()
    {
        return $this->belongsTo(TaxMaster::class, 'tax', 'tax_code');
    }

    public function receivables(){
        return $this->belongsTo(Receivable::class, 'sales_invoice_number', 'document_number');
    }

}
