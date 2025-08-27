<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;



class Professor extends Authenticatable
{
    use Notifiable;

    protected $table = 'professors'; // Updated table name
    protected $primaryKey = 'Prof_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Prof_ID', 'Password', 'Name', 'Email', 'Dept_ID', 'Schedule'   
    ];

    protected $hidden = [
        'Password',
    ];

    // If your password column is named 'Password', override getAuthPassword
    public function getAuthPassword()
    {
        return $this->Password;
    }


    //  public function setPasswordAttribute($value)
    // {
    //     $this->attributes['Password'] = bcrypt($value);
    // }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'professor_subject', 'Prof_ID', 'Subject_ID');
    }
}