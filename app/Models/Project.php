<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'about',
        'category',
        'value',
        'skills',
        'tools',
        'status',
        'user_id'
    ];

    protected $casts = [
        'skills' => 'array',
        'tools' => 'array',
        'value' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
