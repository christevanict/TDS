<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivableList extends Model
{
    use HasFactory;
    protected $table = 'receivable_list';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'receivable_list_number',
        'receivable_list_date',
        'customer_code',
        'periode',
        'total',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function details()
    {
        return $this->hasMany(ReceivableListDetail::class, 'receivable_list_number', 'receivable_list_number');
    }
    public function users()
    {
        return $this->belongsTo(Users::class, 'created_by', 'username'); // assuming 'code' is the primary key in the Company model
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code'); // assuming 'code' is the primary key in the Department model
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code'); // assuming 'code' is the primary key in the Company model
    }
    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }
}
