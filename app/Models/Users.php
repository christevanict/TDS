<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Users extends Authenticatable
{
    use Notifiable;
    use HasFactory;
    protected $table = 'users';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'username',
        'password',
        'role',
        'fullname',
        'department',
        'status',
        'email',
        'created_by',
        'updated_by',
    ];

    public function getAuthIdentifierName()
    {
        return 'username';
    }
    public function departments()
    {
        return $this->belongsTo(Department::class, 'department', 'department_code');
    }

    public function roles(){
        return $this->belongsTo(Role::class,'role','role_number');
    }

    public function deviceToken()
    {
        return $this->hasOne(DeviceToken::class,'user_id');
    }
}
