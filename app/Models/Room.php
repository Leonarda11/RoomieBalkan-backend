<?php

namespace App\Models;

use App\Models\User;
use App\Models\Ad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $fillable = ['name', 'city', 'country', 'price', 'user_id'];

    // Jedna soba ima više oglasa
    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    // Soba pripada jednom korisniku
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}