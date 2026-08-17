<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wedding_id',
        'name',
        'phone_number',
        'group_name',
        'max_pax',
        'token',
        'slug',
        'is_invitation_sent',
        'sent_at',
        'notes',
    ];

    protected $casts = [
        'max_pax' => 'integer',
        'is_invitation_sent' => 'boolean',
        'sent_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function rsvp(): HasOne
    {
        return $this->hasOne(Rsvp::class);
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(Wish::class);
    }

    public function generateToken(): string
    {
        $token = Str::random(64);
        $this->token = $token;

        return $token;
    }
}
