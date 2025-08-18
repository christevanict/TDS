<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_detail';
    protected $softDelete = false; // This should be set to false unless you're using soft deletes
    public $incrementing = true; // Indicates whether the IDs are auto-incrementing
    public $timestamps = true; // Indicates whether the model should use timestamps
    protected $primaryKey = 'id'; // Primary key of the table
    protected $fillable = [
        'purchase_order_number',
        'purchase_requisition_number',
        'item_id', // Ensure this matches your table's column name
        'qty',
        'unit',
        'price',
        'disc_percent',
        'disc_nominal',
        'nominal',
        'number_row',
        'base_qty',
        'base_unit',
        'cancel',
        'qty_left',
        'base_qty_left',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
        'description',
    ];

    // Define relationship to Company model
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }

    // Define relationship to Department model
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code');
    }

    // Define relationship to PurchaseOrder model
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_number', 'purchase_order_number');
    }

    // Define relationship to Item model
    public function items()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_code'); // Assuming item_id is the foreign key in this model
    }

    public function units(){
        return $this->belongsTo(ItemUnit::class,'unit','unit');
    }
    public function baseUnit(){
        return $this->belongsTo(ItemUnit::class,'base_unit','unit');
    }

    // Define relationship to ItemDetail model for base_unit
    public function itemDetail()
    {
        return $this->hasOne(ItemDetail::class, 'item_code', 'item_id'); // Use item_id here
    }
}
