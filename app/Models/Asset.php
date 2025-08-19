<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'asset_code',
        'asset_name',
        'asset_type',
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

    public function assetType()
    {
        return $this->belongsTo(AssetType::class, 'asset_type', 'asset_type_code');
    }

    public function assetDetails()
    {
        return $this->hasMany(AssetDetail::class, 'asset_code', 'asset_code');
    }
}
