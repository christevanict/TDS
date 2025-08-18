<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CogsMethod extends Model
{
    use HasFactory;

    protected $table = 'cogs_methods'; // Nama tabel yang digunakan
    public $incrementing = true; // Penggunaan auto-increment untuk primary key
    public $timestamps = true; // Menyimpan created_at dan updated_at
    protected $primaryKey = 'id'; // Menetapkan primary key
    protected $fillable = [
        'cogs_method', // Nama metode COGS
        'created_by', // ID pengguna yang membuat
        'updated_by', // ID pengguna yang mengupdate
    ];

    /**
     * Relasi dengan pengguna yang membuat metode COGS.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Relasi dengan pengguna yang mengupdate metode COGS.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
