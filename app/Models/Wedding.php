<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wedding extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'status',
        'wedding_date',
        'timezone',
        'cover_title',
        'cover_subtitle',
        'welcome_text',
        'closing_text',
        'theme_config',
        'rsvp_required',
        'comments_need_approval',
        'pax_buffer_percentage',
    ];

    protected $casts = [
        'wedding_date' => 'datetime',
        'theme_config' => 'array',
        'rsvp_required' => 'boolean',
        'comments_need_approval' => 'boolean',
        'pax_buffer_percentage' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->user();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function coupleProfiles(): HasMany
    {
        return $this->hasMany(CoupleProfile::class);
    }

    public function weddingVerses(): HasMany
    {
        return $this->hasMany(WeddingVerse::class);
    }

    public function giftBankAccounts(): HasMany
    {
        return $this->hasMany(GiftBankAccount::class);
    }

    public function giftAddresses(): HasMany
    {
        return $this->hasMany(GiftAddress::class);
    }

    public function documentChecklists(): HasMany
    {
        return $this->hasMany(WeddingDocumentChecklist::class)->orderBy('sort_order')->orderBy('id');
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(Wish::class);
    }

    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
