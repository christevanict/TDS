<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReimburseDetail extends Model
{
    use HasFactory;
    protected $table = 'reimburse_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'reimburse_number',
        'item_description',
        'sales_invoice_vendor',
        'price',
        'account_number',
        'created_by',
        'updated_by',
    ];
}
