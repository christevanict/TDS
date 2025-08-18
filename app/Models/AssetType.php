<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasFactory;
    protected $table = 'asset_type';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'asset_type_code',
        'asset_type_name',
        'economic_life',
        'tariff_depreciation',
        'acc_number_asset',
        'acc_number_akum_depreciation',
        'acc_number_depreciation',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];
    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
    public function department(){
        return $this->belongsTo(Department::class,'department_code','department_code');
    }
    public function economics(){
        return $this->belongsTo(COA::class,'economic_life','account_number');
    }
    public function tariffs(){
        return $this->belongsTo(COA::class,'tariff_depreciation','account_number');
    }
    public function accAsset(){
        return $this->belongsTo(COA::class,'acc_number_asset','account_number');
    }
    public function accAkums(){
        return $this->belongsTo(COA::class,'acc_number_akum_depreciation','account_number');
    }
    public function accDepre(){
        return $this->belongsTo(COA::class,'acc_number_depreciation','account_number');
    }
}
