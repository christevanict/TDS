<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;
    protected $table = 'delivery_order';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'delivery_order_number',
        'document_date',
        'customer_code',
        'notes',
        'department_code',
        'status',
        'created_by',
        'updated_by',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_code','customer_code');
    }

    public function department(){
        return $this->belongsTo(Department::class,'department_code','department_code');
    }

    public function deliveryOrderDetails(){
        return $this->hasMany(DeliveryOrderDetail::class,'delivery_order_number','delivery_order_number');
    }

    public function users(){
        return $this->belongsTo(Users::class,'created_by','username');
    }
}
