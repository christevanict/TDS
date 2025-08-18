<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtOtherDetail extends Model
{
    use HasFactory;
    protected $table = 'debt_other_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'debt_other_number',
        'account_number',
        'nominal',
        'notes',
        'department_code',
        'company_code',
        'created_by',
        'updated_by',
    ];

    public function coa()
    {
        return $this->belongsTo(Coa::class,'account_number','account_number');
    }
}
