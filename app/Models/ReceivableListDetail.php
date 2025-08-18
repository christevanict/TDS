<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivableListDetail extends Model
{
    use HasFactory;
    protected $table = 'receivable_list_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'receivable_list_number',
        'document_number',
        'document_date',
        'customer_code_document',
        'nominal',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function users()
    {
        return $this->belongsTo(Users::class, 'created_by', 'username'); // assuming 'code' is the primary key in the Company model
    }
    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_code_document', 'customer_code');
    }
}
