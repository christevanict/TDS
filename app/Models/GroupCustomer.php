<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCustomer extends Model
{
    use HasFactory;
    protected $table = 'group_customer';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'code_group',
        'name_group',
        'detail_customer_name',
        'company_code',
        'created_by',
        'updated_by',
    ];
}
