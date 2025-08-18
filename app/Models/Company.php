<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;
    protected $table = 'company';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'company_code',
        'company_name',
        'address',
        'phone_number',
        'npwp',
        'pkp',
        'final_tax',
        'type_company',
        'cogs_method',
        'created_by',
        'updated_by',
    ];

    public function type_company():BelongsTo{

        return $this->belongsTo(TypeCompany::class);
    }

    public function cashIns()
    {
        return $this->hasMany(BankCashIn::class, 'company_code', 'company_code');
    }
}
