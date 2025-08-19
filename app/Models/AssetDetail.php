<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDetail extends Model
{
    use HasFactory;

    protected $table = 'asset_details';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'asset_code',
        'asset_name',
        'asset_number',
        'purchase_date',
        'end_economic_life',
        'nominal',
        'is_sold',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_code', 'asset_code');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code');
    }

    public function depreciation()
    {
        return $this->belongsTo(Depreciation::class, 'depreciation_code', 'depreciation_code');
    }
}
