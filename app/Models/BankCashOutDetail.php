<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankCashOutDetail extends Model
{
    use HasFactory;
    protected $table = 'bank_cash_out_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'bank_cash_out_number',
        'account_number_header',
        'account_number',
        'nominal',
        'note',
        'row_number',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code');
    }

    public function coa(){
        return $this->belongsTo(Coa::class,'account_number','account_number');
    }
}
