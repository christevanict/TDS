<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankCashIn extends Model
{
    use HasFactory;
    protected $table = 'bank_cash_in';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'bank_cash_in_number',
        'bank_cash_in_date',
        'account_number',
        'nominal',
        'note',
        'token',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code'); // assuming 'code' is the primary key in the Company model
    }


    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code'); // assuming 'code' is the primary key in the Department model
    }


    public function details() {
        return $this->hasMany(BankCashInDetail::class, 'bank_cash_in_number', 'bank_cash_in_number');
    }

    public function coa(){
        return $this->belongsTo(Coa::class, 'account_number', 'account_number');
    }

    public function users(){
        return $this->belongsTo(Users::class, 'created_by', 'username');
    }
}
