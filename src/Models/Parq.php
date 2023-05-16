<?php

namespace App\Models;

use App\Traits\Logable;
use App\Models\Core\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parq extends Model
{
    use Logable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'dob',
        'accept',
        'email',
        'questions',
        'emergency_contact_name',
        'emergency_contact_phone_number',
        'allergies',
        'seen',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'accept' => 'boolean',
        'seen' => 'boolean',
        'questions' => 'collection',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'updated_by.admin',
    ];

    /**
     * Get the user that owns the Parq
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updated_by()
    {
        return $this->morphOne(Log::class, 'logable')->latestOfMany();
    }
}
