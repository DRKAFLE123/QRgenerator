<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BioPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bio_pages';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'theme',
        'links',
        'website',
        'logo_path',
        'cover_path',
        'status',
        'payment_status',
        'expires_at',
        'color',
        'bg_color',
        'permalink'
    ];

    protected $casts = [
        'links' => 'array',
        'expires_at' => 'datetime',
    ];
}
