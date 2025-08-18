<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeCompany extends Model
{
    use HasFactory;
    protected $table = 'type_company';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'type_company',
        'created_by',
        'updated_by',
    ];

    public function companies():HasMany{
        return $this->hasMany(Company::class,'type_company','type_company');
    }
}
