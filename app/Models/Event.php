<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'title',
        'date',
        'start_time',
        'end_time',
        'venue_name',
        'address',
        'google_maps_url',
        'latitude',
        'longitude',
        'sort_order',
    ];

    protected $casts = [
        'date' => 'date',
        'sort_order' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }
}
