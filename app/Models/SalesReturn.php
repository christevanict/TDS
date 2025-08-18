<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;
    protected $table = 'sales_return';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'sales_return_number',
        'document_date',
        'due_date',
        'customer_code',
        'sales_invoice_number',
        'tax',
        'include',
        'subtotal',
        'disc_percent',
        'disc_nominal',
        'tax_revenue',
        'add_tax',
        'total',
        'token',
        'notes',
        'recap',
        'account_number',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }


    // Define the relationship with the Company model
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }

    // Define the relationship with the Department model
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code');
    }


    // You can also define a relationship for sales order details if applicable
    public function details()
    {
        return $this->hasMany(SalesReturnDetail::class, 'sales_return_number', 'sales_return_number');
    }

    public function taxs()
    {
        return $this->belongsTo(TaxMaster::class, 'tax', 'tax_code');
    }

    public function debts(){
        return $this->hasMany(Debt::class, 'document_number', 'sales_return_number');
    }
    public function users()
    {
        return $this->belongsTo(Users::class, 'created_by', 'username'); // assuming 'code' is the primary key in the Company model
    }
}
