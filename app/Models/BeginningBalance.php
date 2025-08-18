<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeginningBalance extends Model
{
    use HasFactory;
    protected $table = 'beginning_balance';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'account_number',
        'begin_debet_nominal',
        'begin_credit_nominal',
        'adjust_debit_nominal',
        'adjust_credit_nominal',
        'ending_debet_balance',
        'ending_credit_balance',
        'periode',
        'created_by',
        'updated_by',
    ];
}
