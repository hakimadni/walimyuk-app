<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingVerse extends Model
{
    use HasFactory;

    protected $table = 'wedding_verses';

    protected $fillable = [
        'wedding_id',
        'source_label',
        'arabic_text',
        'transliteration',
        'translation',
    ];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }
}
