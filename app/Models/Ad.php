<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'city',
        'country',
        'user_id',
        'image_url',
        'images',
        'created_at',
        'updated_at',
        'room_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
        public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
