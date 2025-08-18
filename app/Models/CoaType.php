<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class CoaType extends Model
{
    use HasFactory;
    protected $table = 'coa_type';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'account_sub_type',
        'account_type',
        'normal_balance',
        'company_code',
        'created_by',
        'updated_by',
    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
}
