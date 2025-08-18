<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemSalesPrice extends Model
{
    use HasFactory;

    protected $table = 'item_sales_price'; // Nama tabel yang terkait

    protected $primaryKey = 'id'; // Auto-increment primary key

    public $incrementing = true; // Menggunakan auto-increment

    public $timestamps = true; // Mengelola timestamps secara otomatis

    protected $fillable = [
        'barcode',
        'item_code',
        'sales_price',
        'unit',
        'category_customer',
        'department_code',
        'company_code',
        'created_by',
        'updated_by',
    ];

    public function items()
    {
        return $this->belongsTo(Item::class, 'item_code', 'item_code');
    }

    public function units()
    {
        return $this->belongsTo(ItemDetail::class, 'unit', 'unit_conversion');
    }
    public function barcodes(){
        return $this->belongsTo(ItemDetail::class,'barcode','barcode');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }
    public function unitn(){
        return $this->belongsTo(ItemUnit::class,'unit','unit');
    }

    public function CategoryCustomer()
    {
        return $this->belongsTo(CategoryCustomer::class, 'category_customer', 'category_code');
    }

    public function itemDetails(){
        return $this->hasMany(ItemDetail::class,'item_code','item_code');
    }
}
