<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointOfSale extends Model
{
    use HasFactory;

    // Nama tabel (jika tidak mengikuti konvensi pluralisasi, seperti misalnya 'point_of_sale')
    protected $table = 'point_of_sales';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'pos_number',
        'transaction_date',
        'total_amount',
        'discount',
        'final_amount',
        'payment_method',
        'cash_received',
        'change',
        'voucher_code',
        'created_by',
        'customer_id',
    ];

    /**
     * Mendefinisikan hubungan dengan model PointOfSaleDetail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(PointOfSaleDetail::class, 'point_of_sale_id');
    }

    /**
     * Mendefinisikan hubungan dengan model Customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id'); // Pastikan relasi ke customer_id
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Mendefinisikan hubungan dengan model Users (pengguna yang membuat transaksi).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'created_by'); // Menentukan kolom 'created_by' untuk relasi ke pengguna
    }

    /**
     * Menambahkan kolom tanggal untuk pemrosesan otomatis.
     *
     * @var array
     */
    protected $dates = [
        'transaction_date', // Mengonversi kolom ini menjadi instance Carbon otomatis
    ];

    /**
     * Untuk memformat beberapa atribut (misalnya uang) dalam bentuk yang lebih sesuai.
     *
     * @var array
     */
    protected $casts = [
        'total_amount' => 'float',
        'discount' => 'float',
        'final_amount' => 'float',
        'cash_received' => 'float',
        'change' => 'float',
    ];
}
