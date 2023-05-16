<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'release_at',
        'type',
        'disabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'release_at' => 'datetime:d/m/Y',
        'disabled' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'active'
    ];

    /**
     * Get the active
     *
     * @return bool
     */
    public function getActiveAttribute()
    {
        return $this->isActive();
    }

    public function isActive()
    {
        return $this->release_at->gt(now());
    }

    /**
     * Scope a query to only include unblocked
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnblocked($query)
    {
        return $query->whereDate('release_at', '<=', now())->orWhere('disabled', 1);
    }

    /**
     * Scope a query to only include blocked
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBlocked($query)
    {
        return $query->whereDate('release_at', '>', now())->where('disabled', 0);
    }
}
