<?php

namespace CoderstmCore\Models;

use CoderstmCore\Models\User;
use CoderstmCore\Traits\Base;
use CoderstmCore\Events\ReferralCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use Base;

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => ReferralCreated::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'note',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'user',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * Get the full name of the user.
     *
     * @return bool
     */
    public function getNameAttribute()
    {
        return "{$this->title} {$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user that owns the Referral
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withOnly([]);
    }

    /**
     * Scope a query to only include whereName
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereName($query, $filter)
    {
        return $query->where(DB::raw("CONCAT(`first_name`,`last_name`)"), 'like', "%{$filter}%");
    }

    /**
     * Scope a query to only include onlyOwner
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyOwner($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('id', currentUser()->id);
        });
    }

    /**
     * Scope a query to only include sortBy
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBy($query, $column = 'CREATED_AT_ASC', $direction = 'asc')
    {
        switch ($column) {
            case 'name':
                $query->select("{$this->getTable()}.*")
                    ->addSelect(DB::raw("CONCAT(`first_name`, `first_name`) AS name"))
                    ->orderBy('name', $direction ?? 'asc');
                break;

            default:
                $query->orderBy($column ?: 'created_at', $direction ?? 'asc');
                break;
        }

        return $query;
    }

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            if (empty($model->user_id)) {
                $model->user_id = optional(currentUser())->id;
            }
        });
    }
}
