<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointOfSaleDetail extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan dalam model ini
    protected $table = 'point_of_sale_details';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'point_of_sale_id', // ID dari transaksi point of sale (relasi ke PointOfSale)
        'item_id',          // ID dari item yang dibeli
        'item_name',        // Nama item yang dibeli (bisa saja hanya untuk tujuan tampilan)
        'price',            // Harga per unit item
        'quantity',         // Jumlah item yang dibeli
        'subtotal',         // Subtotal (harga * kuantitas)
    ];

    /**
     * Mendefinisikan hubungan dengan model PointOfSale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pointOfSale()
    {
        return $this->belongsTo(PointOfSale::class, 'point_of_sale_id');
    }

    /**
     * Mendefinisikan hubungan dengan model Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Menambahkan kolom subtotal untuk mempermudah perhitungan.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',       // Pastikan harga adalah tipe data float
        'quantity' => 'integer',  // Kuantitas adalah tipe data integer
        'subtotal' => 'float',    // Subtotal adalah hasil perkalian harga * kuantitas
    ];
}
