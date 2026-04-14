<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pharmacy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'city',
        'address',
        'phone',
        'status',
        'is_subscribed',
        'subscription_plan',
        'subscription_amount',
        'subscription_paid_at',
        'subscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_subscribed' => 'boolean',
            'subscription_amount' => 'decimal:2',
            'subscription_paid_at' => 'datetime',
            'subscribed_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function adverts(): HasMany
    {
        return $this->hasMany(Advert::class);
    }
}
