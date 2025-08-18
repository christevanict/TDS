<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryCustomer extends Model
{
    use HasFactory;

    protected $table = 'category_customers'; // Name of the table used
    public $incrementing = false; // Disable auto-incrementing since category_code is the primary key
    public $timestamps = true; // Store created_at and updated_at timestamps
    protected $primaryKey = 'category_code'; // Set the primary key to category_code
    protected $fillable = [
        'category_code', // Unique identifier for the category
        'category_name', // Name of the category
        'company_code',  // Company associated with the category
        'created_by',    // ID of the user who created the record
        'updated_by',    // ID of the user who last updated the record
    ];

    /**
     * Relationship with the user who created the category.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Relationship with the user who updated the category.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
}
