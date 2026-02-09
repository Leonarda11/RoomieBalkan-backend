<?php

namespace App\Models;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Koja polja se mogu masovno upisivati
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'room_id',
    ];

    // Polja koja će biti skrivena u JSON-u
    protected $hidden = [
        'password',
        'remember_token',
    ];

   
    protected $casts = [
        'email_verified_at' => 'datetime',
     
    ];

    // Relacija: korisnik pripada jednoj sobi
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
