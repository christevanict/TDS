<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;
    protected $table = 'purchase_return';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'purchase_return_number',
        'document_date',
        'supplier_code',
        'purchase_invoice_number',
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

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'supplier_code');
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


    // You can also define a relationship for purchase order details if applicable
    public function details()
    {
        return $this->hasMany(PurchaseReturnDetail::class, 'purchase_return_number', 'purchase_return_number');
    }

    public function taxs()
    {
        return $this->belongsTo(TaxMaster::class, 'tax', 'tax_code');
    }

    public function debts(){
        return $this->hasOne(Debt::class, 'document_number', 'purchase_return_number');
    }
    public function users()
    {
        return $this->belongsTo(Users::class, 'created_by', 'username'); // assuming 'code' is the primary key in the Company model
    }


}
