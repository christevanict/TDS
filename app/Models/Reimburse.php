<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reimburse extends Model
{
    use HasFactory;
    protected $table = 'reimburse';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'reimburse_number',
        'document_date',
        'due_date',
        'contract_document_number',
        'total',
        'created_by',
        'updated_by',
    ];

    public function details()
    {
        return $this->hasMany(ReimburseDetail::class, 'reimburse_number', 'reimburse_number');
    }
    public function sos()
    {
        return $this->belongsTo(SalesOrder::class, 'contract_document_number', 'sales_order_number');
    }
    public function users()
    {
        return $this->belongsTo(Users::class, 'created_by', 'username'); // assuming 'code' is the primary key in the Company model
    }
}
