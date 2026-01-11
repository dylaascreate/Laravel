<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'status',
        'description',
        'skills'
    ];

    protected $casts = [
        'skills' => 'array', // Automatically cast JSON to array
    ];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
