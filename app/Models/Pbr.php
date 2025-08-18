<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pbr extends Model
{
    use HasFactory;
    protected $table = 'pbr';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'pbr_number',
        'pbr_order_number',
        'document_date',
        'delivery_date',
        'customer_code',
        'disc_nominal',
        'subtotal',
        'tax',
        'add_tax',
        'tax_revenue',
        'total',
        'status',
        'department_code',
        'company_code',
        'tax_revenue_tariff',
        'notes',
        'reason',
        'created_by',
        'updated_by',
    ];

    public function users()
    {
        return $this->belongsTo(Users::class, 'created_by', 'username');
    }

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }

    public function details()
    {
        return $this->hasMany(PbrDetail::class,'pbr_number','pbr_number');
    }

    public function department()
    {
        return $this->belongsTo(Department::class,'department_code','department_code');
    }


}
