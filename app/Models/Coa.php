<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Coa extends Model
{
    use HasFactory;
    protected $table = 'coa';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'account_number',
        'account_name',
        'account_type',
        'normal_balance',
        'company_code',
        'created_by',
        'updated_by',
    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
    public function coasss(){
        return $this->belongsTo(CoaType::class,'account_type','id');
    }

    public function journals(){
        return $this->hasMany(Journal::class,'account_number','account_number');
    }


}
