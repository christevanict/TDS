<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depreciation extends Model
{
    use HasFactory;
    protected $table = 'depreciation';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'depreciation_code',
        'depreciation_name',
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
}
