<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $fillable = ['user_id', 'device_token', 'user_agent','department_code', 'last_login_at'];

    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');
    }
}
