<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivableListSalesman extends Model
{
    use HasFactory;
    protected $table = 'receivable_list_salesman';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'receivable_list_salesman_number',
        'receivable_list_salesman_date',
        'city_code',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function details()
    {
        return $this->hasMany(ReceivableListSalesmanDetail::class, 'receivable_list_salesman_number', 'receivable_list_salesman_number');
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
}
