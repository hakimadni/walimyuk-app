<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'role',
        'full_name',
        'nickname',
        'father_name',
        'mother_name',
        'child_order_text',
        'photo_path',
        'instagram_url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }
}
