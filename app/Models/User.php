<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string $role
 */
final class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status'            => 'integer',
        'is_email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'token_expires_at'  => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Get the bookings made by this user.
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    /**
     * Get the partner info for this user (if user is a partner).
     *
     * @return HasOne
     */
    public function partnerInfo(): HasOne
    {
        return $this->hasOne(PartnerInfo::class, 'user_id');
    }

    /**
     * Properties this user owns (partner dashboard).
     *
     * @return HasMany<Property>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'user_id');
    }

    /**
     * Check if user has specific role
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Summary of getJWTIdentifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Summary of getJWTCustomClaims
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get reviews written by this user.
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    /**
     * Get reviews received by this partner user.
     *
     * @return HasMany
     */
    public function partnerReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'partner_id');
    }
}
