<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeleteLog extends Model
{
    use HasFactory;
    protected $table = 'delete_log';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'document_number',
        'document_date',
        'delete_notes',
        'company_code',
        'department_code',
        'deleted_by',
        'type',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class,'department_code','department_code');
    }
}
