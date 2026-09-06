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
        'short_code',
        'slug',
        'is_invitation_sent',
        'sent_at',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            if (empty($guest->token)) {
                $guest->token = Str::random(64);
            }
            if (empty($guest->short_code)) {
                $guest->short_code = static::generateUniqueShortCode();
            }
        });
    }

    public static function generateUniqueShortCode(int $length = 6): string
    {
        do {
            $code = Str::lower(Str::random($length));
        } while (static::withTrashed()->where('short_code', $code)->exists());

        return $code;
    }

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
