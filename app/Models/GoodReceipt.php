<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodReceipt extends Model
{
    use HasFactory;
    protected $table = 'good_receipt';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'good_receipt_number',
        'document_date',
        'supplier_code',
        'notes',
        'warehouse_code',
        'department_code',
        'vendor_number',
        'status',
        'token',
        'created_by',
        'updated_by',
        'cancel_notes',
    ];

    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_code','supplier_code');
    }

    public function department(){
        return $this->belongsTo(Department::class,'department_code','department_code');
    }

    public function goodReceiptDetails(){
        return $this->hasMany(GoodReceiptDetail::class,'good_receipt_number','good_receipt_number');
    }

    public function users(){
        return $this->belongsTo(Users::class,'created_by','username');
    }

    public function warehouse(){
        return $this->belongsTo(Warehouse::class,'warehouse_code','warehouse_code');
    }
}
