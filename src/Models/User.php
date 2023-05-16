<?php

namespace App\Models;

use App\Enum\AppRag;
use App\Models\Parq;
use App\Enum\AppStatus;
use App\Models\Core\Log;
use App\Traits\Billable;
use App\Models\Core\File;
use App\Models\Plan\Price;
use App\Models\Core\Enquiry;
use App\Traits\HasBelongsToOne;
use Illuminate\Support\Facades\DB;
use App\Models\Cashier\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Stripe\Subscription as StripeSubscription;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class User extends Admin implements MustVerifyEmail
{
    use HasBelongsToOne, Billable;

    protected $guard = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'is_active',
        // extra
        'title',
        'member_id',
        'note',
        'status',
        'source',
        'gender',
        'rag',
        'plan_id',
        'collect_id',
        'admin_id',
        'checked',
        'request_parq',
        'request_avatar',
        'release_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'rag' => AppRag::class,
        'status' => AppStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean',
        'request_parq' => 'boolean',
        'request_avatar' => 'boolean',
        'checked' => 'boolean',
        'created_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'member_since',
        'member_id_formated',
        'guard',
        'has_avatar',
        'has_parq',
        'has_blocked',
        'subscribed',
        'has_cancelled',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'avatar',
        'address',
        'lastLogin',
        'latestInvoice',
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
     * Get the member since of the user.
     *
     * @return bool
     */
    public function getMemberSinceAttribute()
    {
        return $this->created_at->format('Y');
    }

    /**
     * Get the member id formated of the user.
     *
     * @return string
     */
    public function getMemberIdFormatedAttribute()
    {
        return "{$this->member_id}-28{$this->id}";
    }

    /**
     * Get the is enquiry of the user.
     *
     * @return bool
     */
    public function getIsEnquiryAttribute($value)
    {
        return $this->status != AppStatus::ACTIVE;
    }

    /**
     * Get the has parq of the user.
     *
     * @return bool
     */
    public function getHasParqAttribute()
    {
        return !empty($this->parq);
    }

    /**
     * Get the has blocked of the user.
     *
     * @return bool
     */
    public function getHasBlockedAttribute()
    {
        return $this->isBlocked() && !$this->blocked->disabled;
    }

    /**
     * Get the has avatar of the user.
     *
     * @return bool
     */
    public function getHasAvatarAttribute()
    {
        return !empty($this->avatar);
    }

    public function notes()
    {
        return $this->morphMany(Log::class, 'logable')
            ->whereNotIn('type', ['login'])
            ->orderBy('created_at', 'desc')
            ->withOnly(['admin']);
    }

    public function lastUpdate()
    {
        return $this->morphOne(Log::class, 'logable')->where('type', 'notes')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all of the bookings for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the last no show bookings associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastNsBookings(): HasOne
    {
        return $this->hasOne(Booking::class)
            ->withOnly(['schedule'])
            ->onlyLastWeekNoShow()
            ->orderBy('schedules_at', 'desc');
    }

    /**
     * Get the last late cancellation associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastLateCancellation(): HasOne
    {
        return $this->hasOne(Booking::class)
            ->withOnly(['schedule'])
            ->onlyLastWeekLateCancellation()
            ->orderBy('schedules_at', 'desc');
    }

    /**
     * The schedules that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(ClassSchedule::class, 'bookings', 'user_id', 'schedule_id');
    }

    /**
     * Get the blocked associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function blocked(): HasOne
    {
        return $this->hasOne(Block::class);
    }

    public function isBlocked()
    {
        return $this->blocked && $this->blocked->isActive();
    }

    public function updateOrCreateBlocked(array $attributes = [])
    {
        return $this->blocked()->updateOrCreate([
            'user_id' => $this->id
        ], [
            'disabled' => optional((object) $attributes)->disabled ?? false,
            'release_at' => optional((object) $attributes)->release_at ?? now()->addDays(3),
            'type' => optional((object) $attributes)->type ?? 'NS',
        ]);
    }

    public function updateEndsAt($endsAt = null)
    {
        if ($this->subscription()) {
            $this->subscription()->update([
                'cancels_at' => $endsAt,
            ]);
        }
        return $this;
    }

    /**
     * Get all of the invoices for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function appInvoices()
    {
        return $this->hasManyThrough(Invoice::class, Subscription::class);
    }

    /**
     * Get the latest invoices for the User
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function latestInvoice()
    {
        return $this->hasOneThrough(Invoice::class, Subscription::class)->orderByDesc('created_at');
    }

    /**
     * Get the plan that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * The price that belong to the User
     *
     * @return \App\Relations\BelongsToOne
     */
    public function price()
    {
        return $this->belongsToOne(Price::class, 'subscriptions', 'user_id', 'stripe_price', 'id', 'stripe_id')
            ->orderByDesc('created_at');
    }

    /**
     * Get the parq associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parq(): HasOne
    {
        return $this->hasOne(Parq::class);
    }

    /**
     * The documents that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'read_documents', 'user_id', 'file_id');
    }

    public function updateOrCreateParq(array $parq)
    {
        if ($this->parq) {
            return $this->parq()->update((new Parq($parq))->toArray());
        } else {
            return $this->parq()->save(new Parq($parq));
        }
    }

    /**
     * Get all of the enquiries for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'email', 'email');
    }

    /**
     * Eager load unread enquiries counts on the User.
     *
     * @return $this
     */
    public function loadUnreadEnquiries()
    {
        return $this->loadCount([
            'enquiries as unread_enquiries' => function (Builder $query) {
                $query->onlyActive();
            }
        ]);
    }

    /**
     * Scope a query to only include onlyActive
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyActive($query)
    {
        return $query->where([
            'status' => AppStatus::ACTIVE
        ]);
    }

    /**
     * Scope a query to only include onlyMember
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyMember($query)
    {
        return $query->where([
            'status' => AppStatus::ACTIVE
        ]);
    }

    /**
     * Scope a query to only include onlyEnquiry
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyEnquiry($query)
    {
        return $query->where('status', '<>', AppStatus::ACTIVE);
    }

    /**
     * Scope a query to only include onlyNoShow
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyNoShow($query)
    {
        return $query->withCount([
            'bookings as ns_bookings_count' => function ($query) {
                $query->onlyLastWeekNoShow();
            },
        ])->whereHas('bookings', function ($booking) {
            $booking->onlyLastWeekNoShow();
        });
    }

    /**
     * Scope a query to only include onlyBlocked
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyBlocked($query)
    {
        return $query->with('blocked')->whereHas('blocked', function ($booking) {
            $booking->blocked();
        });
    }

    /**
     * Scope a query to only include onlyUnblocked
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyUnblocked($query)
    {
        return $query->doesntHave('blocked')->orWhereHas('blocked', function ($booking) {
            $booking->unblocked();
        });
    }

    /**
     * Scope a query to only include onlyLateCancellation
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyLateCancellation($query)
    {
        return $query->withCount([
            'bookings as late_cancellation_count' => function ($query) {
                $query->onlyLastWeekLateCancellation();
            },
        ])->whereHas('bookings', function ($booking) {
            $booking->onlyLastWeekLateCancellation();
        });
    }

    /**
     * Scope a query to only include onlyChecked
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param   int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyChecked($query, int $type = 1)
    {
        return $query->where('checked', $type);
    }

    /**
     * Scope a query to only include onlyParq
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param   bool $has
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyParq($query, bool $has = true)
    {
        if ($has) {
            return $query->has('parq');
        }
        return $query->doesntHave('parq');
    }

    /**
     * Scope a query to only include onlyNoParq
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyNoParq($query)
    {
        return $query->onlyParq(false);
    }

    /**
     * Scope a query to only include onlyPic
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param   bool $has
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyPic($query, bool $has = true)
    {
        if ($has) {
            return $query->has('avatar');
        }
        return $query->doesntHave('avatar');
    }

    /**
     * Scope a query to only include onlyNoPic
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyNoPic($query)
    {
        return $query->onlyPic(false);
    }

    /**
     * Scope a query to only include onlyCancelled
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param   int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyCancelled($query)
    {
        return $query->whereHas('subscriptions', function ($q) {
            $q->canceled();
        });
    }

    /**
     * Scope a query to only include onlyMonthlyPlan
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyMonthlyPlan($query)
    {
        return $query->onlyPlan('month');
    }

    /**
     * Scope a query to only include onlyYearlyPlan
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyYearlyPlan($query)
    {
        return $query->onlyPlan('year');
    }

    /**
     * Scope a query to only include onlyPlan
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param   string $type year|month|day
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyPlan($query, string $type = 'month')
    {
        return $query->whereHas('subscriptions', function ($q) use ($type) {
            $q->active()
                ->whereNull('cancels_at')
                ->whereHas('price', function ($q) use ($type) {
                    $q->whereInterval($type)
                        ->where('amount', '<>', 0);
                });
        });
    }

    /**
     * Scope a query to only include onlyRolling
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyRolling($query)
    {
        return $query->whereHas('subscriptions', function ($q) {
            $q->active()->whereNull('cancels_at');
        });
    }

    /**
     * Scope a query to only include onlyEnds
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyEnds($query)
    {
        return $query->whereHas('subscriptions', function ($q) {
            $q->active()->whereNotNull('cancels_at');
        });
    }

    /**
     * Scope a query to only include onlyFree
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyFree($query)
    {
        return $query->whereHas('subscriptions', function ($q) {
            $q->active()->whereHas('price', function ($q) {
                $q->whereAmount(0);
            });
        });
    }

    /**
     * Scope a query to only include whereTyped
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereTyped($query, string $type = null)
    {
        switch ($type) {
            case 'checked':
                $query->onlyChecked();
                break;

            case 'notchecked':
                $query->onlyChecked(0);
                break;

            case 'parq':
                $query->onlyParq();
                break;

            case 'notparq':
                $query->onlyNoParq();
                break;

            case 'pic':
                $query->onlyPic();
                break;

            case 'notpic':
                $query->onlyNoPic();
                break;

            case 'rolling':
                $query->onlyRolling();
                break;

            case 'ends':
            case 'end_date':
                $query->onlyEnds();
                break;

            case 'month':
            case 'year':
                $query->onlyPlan($type);
                break;

            case 'free':
                $query->onlyFree();
                break;
        }

        return $query;
    }

    /**
     * Scope a query to only include sumAmount
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  bool $cancelled
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSumAmount($query, $cancelled = false)
    {
        return $query->select('users.id', "plan_prices.amount")->leftJoin('subscriptions', function ($join) use ($cancelled) {
            $join->on('subscriptions.user_id', '=', "users.id")->where('stripe_status', $cancelled ? StripeSubscription::STATUS_CANCELED : StripeSubscription::STATUS_ACTIVE)->limit(1);
        })->leftJoin('plan_prices', function ($join) {
            $join->on('plan_prices.stripe_id', '=', "subscriptions.stripe_price");
        })->sum("plan_prices.amount");
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
            case 'last_login':
                $query->select("users.*")
                    ->leftJoin('logs', function ($join) {
                        $join->on('logs.logable_id', '=', "users.id")
                            ->where('logs.logable_type', '=', $this->getMorphClass())
                            ->where('logs.type', 'login');
                    })
                    ->addSelect(DB::raw('logs.created_at AS last_login_at'))
                    ->groupBy("users.id")
                    ->orderBy('last_login_at', $direction ?? 'asc');
                break;

            case 'last_update':
                $query->select("users.*")
                    ->leftJoin('logs', function ($join) {
                        $join->on('logs.logable_id', '=', "users.id")
                            ->where('logs.logable_type', '=', $this->getMorphClass())
                            ->where('logs.type', 'notes');
                    })
                    ->addSelect(DB::raw('logs.created_at AS last_update_at'))
                    ->groupBy("users.id")
                    ->orderBy('last_update_at', $direction ?? 'asc');
                break;

            case 'created_by':
                $query->select("users.*")
                    ->leftJoin('logs', function ($join) {
                        $join->on('logs.logable_id', '=', "users.id")
                            ->where('logs.logable_type', '=', $this->getMorphClass())
                            ->where('logs.type', 'created');
                    })
                    ->leftJoin('admins', function ($join) {
                        $join->on('logs.admin_id', '=', "admins.id");
                    })
                    ->addSelect(DB::raw('CASE WHEN logs.admin_id IS NOT NULL THEN admins.first_name ELSE JSON_EXTRACT(logs.options, "$.ref") END AS created_by'))
                    ->groupBy("users.id")
                    ->orderBy('created_by', $direction ?? 'asc');
                break;

            case 'last_ns_bookings':
                $query->orderBy(Booking::onlyLastWeekNoShow()->limit(1)->select('schedules_at')->whereColumn('bookings.user_id', 'users.id'), $direction ?? 'asc');
                break;

            case 'email':
                $query->orderBy('email', $direction ?? 'asc');
                break;

            case 'name':
                $query->orderBy(DB::raw("CONCAT(`first_name`, `last_name`)"), $direction ?? 'asc');
                break;

            default:
                $query->orderBy($column ?: 'created_at', $direction ?? 'asc');
                break;
        }

        return $query;
    }

    /**
     * Scope a query to only include withUnreadEnquiries
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithUnreadEnquiries($query)
    {
        return $query->withCount([
            'enquiries as unread_enquiries' => function (Builder $query) {
                $query->onlyActive();
            },
        ]);
    }

    /**
     * Scope a query to only include whereDateColumn
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $date
     * @param  string $column
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereDateColumn($query, $date = [], $column = 'created_at')
    {
        return $query->whereHas('subscriptions', function ($q) use ($date, $column) {
            if (isset($date['year'])) {
                $q->whereYear($column, $date['year']);
            }
            if (isset($date['month'])) {
                $q->whereMonth($column, $date['month']);
            }
        });
    }

    static public function getStatsByMonthAndYear($key, $month = null, $year = null)
    {
        $user = static::onlyMember()->whereDateColumn(['month' => $month, 'year' => $year]);
        $cancelled = static::onlyCancelled()->whereDateColumn(['month' => $month, 'year' => $year], 'ends_at');

        switch ($key) {
            case 'total':
                return $user->count();
                break;

            case 'rolling':
                return $user->onlyRolling()->count();
                break;

            case 'rolling_total':
                return $user->onlyRolling()->sumAmount();
                break;

            case 'end_date':
                return $user->onlyEnds()->count();
                break;

            case 'end_date_total':
                return $user->onlyEnds()->sumAmount();
                break;

            case 'month':
            case 'year':
                return $user->onlyPlan($key)->count();
                break;

            case 'free':
                return $user->onlyFree()->count();
                break;

            case 'cancelled':
                return $cancelled->count();
                break;

            case 'cancelled_total':
                return $cancelled->sumAmount(true);
                break;

            default:
                return 0;
                break;
        }
    }

    static public function getStats($key)
    {
        return static::getStatsByMonthAndYear($key);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->member_id)) {
                $model->member_id = now()->format('dmy');
            }
        });
        static::updated(function ($model) {
            Enquiry::withoutEvents(function () use ($model) {
                Enquiry::where('email', $model->getOriginal('email'))->update([
                    'email' => $model->email
                ]);
            });
        });
        static::addGlobalScope('default', function (Builder $builder) {
            $builder->withCount([
                'enquiries as unread_enquiries' => function (Builder $query) {
                    $query->onlyActive();
                },
            ])
                ->withMax('subscriptions as ends_at', 'cancels_at')
                ->withMax('subscriptions as starts_at', 'created_at');
        });
    }
}
