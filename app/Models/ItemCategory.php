<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    use HasFactory;
    protected $table = 'item_category';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'item_category_code',
        'item_category_name',
        'company_code',
        'acc_number_purchase',
        'acc_number_purchase_return',
        'acc_number_purchase_discount',
        'acc_number_sales',
        'acc_number_sales_return',
        'acc_number_sales_discount',
        'acc_number_grpo',
        'acc_number_do',
        'acc_number_wip',
        'acc_number_wip_variance',
        'account_inventory',
        'acc_cogs',
        'acc_barang_rusak',
        'created_by',
        'updated_by',
    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
}
