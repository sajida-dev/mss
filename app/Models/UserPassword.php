<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPassword extends Model
{
    protected $fillable = ['user_id', 'password_encrypted'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
