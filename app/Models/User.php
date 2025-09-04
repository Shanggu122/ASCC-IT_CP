<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\HasProfilePhoto;

class User extends Authenticatable
{
    use Notifiable, HasProfilePhoto;

    protected $table = 't_student';
    protected $primaryKey = 'Stud_ID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'Stud_ID', 'Name', 'Dept_ID', 'Email', 'Password' , 'profile_picture'
    ];

    protected $hidden = [
        'Password',
    ];

    // Laravel expects 'password' field, so let's override
    public function getAuthPassword()
    {
        return $this->Password;
    }

    // Switch to professor context

}
