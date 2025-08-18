<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemSalesNt extends Model
{
    use HasFactory;
    protected $table = 'item_sales_nt'; // Nama tabel yang terkait

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
}
