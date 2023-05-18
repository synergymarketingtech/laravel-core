<?php

namespace Coderstm\Models;

use Coderstm\Models\User;
use Coderstm\Traits\Base;
use Coderstm\Enum\AppStatus;
use Coderstm\Traits\Fileable;
use Coderstm\Models\Shop\Order;
use Coderstm\Events\EnquiryCreated;
use Coderstm\Models\Enquiry\Reply;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    use Base, Fileable;

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => EnquiryCreated::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'seen',
        'is_archived',
        'user_archived',
        'order_id',
        'source',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'last_reply.user',
        'user',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'has_unseen',
    ];

    /**
     * The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = [
        'unseen',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => AppStatus::class,
        'seen' => 'boolean',
        'is_archived' => 'boolean',
        'user_archived' => 'boolean',
        'created_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * Get the has unseen
     *
     * @return bool
     */
    public function getHasUnseenAttribute()
    {
        return $this->unseen_count > 0 || !$this->seen;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getNameAttribute($value)
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $value;
    }

    /**
     * Get the phone
     *
     * @return string
     */
    public function getPhoneAttribute($value)
    {
        if ($this->user) {
            return $this->user->phone_number;
        }
        return $value;
    }

    /**
     * Get the user that owns the Enquiry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email')->withOnly([]);
    }

    /**
     * Get the order that owns the Enquiry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withOnly(['line_items']);
    }

    /**
     * Get all of the replies for the Enquiry
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Reply::class, 'enquiry_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the enquiry's most recent reply.
     */
    public function last_reply()
    {
        return $this->hasOne(Reply::class, 'enquiry_id')->latestOfMany();
    }

    /**
     * Get all of the unseen replies for the Enquiry
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unseen()
    {
        return $this->hasMany(Reply::class, 'enquiry_id')->unseen();
    }

    public function markedAsSeen()
    {
        $this->seen = true;
        $this->unseen()->update([
            'seen' => true
        ]);
        $this->save();
        return $this;
    }

    public function createReply(array $attributes = [])
    {
        $reply = new Reply($attributes);
        $reply->user()->associate(currentUser());
        return $this->replies()->save($reply);
    }

    /**
     * Scope a query to only include whereType
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereType($query, $type)
    {
        switch ($type) {
            case 'users':
                return $query->whereNotNull('subject');
                break;

            default:
                return $query->whereNull('subject');
                break;
        }
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
     * Scope a query to only include onlyUnread
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyUnread($query)
    {
        return $query->where('status', AppStatus::STAFF_REPLIED);
    }

    /**
     * Scope a query to only include onlyActive
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyActive($query)
    {
        return $query->onlyStatus('Live');
    }

    /**
     * Scope a query to only include onlyStatus
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string|null $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyStatus($query, $status = null)
    {
        switch ($status) {
            case 'Live':
                if (is_user()) {
                    return $query->whereUserArchived(0);
                } else {
                    return $query->whereIsArchived(0);
                }
                break;

            case 'Archive':
                if (is_user()) {
                    return $query->whereUserArchived(1);
                } else {
                    return $query->whereIsArchived(1);
                }
                break;
        }

        return $query;
    }



    /**
     * Scope a query to only include sortBy
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBy($query, $column = 'created_at', $direction = 'asc')
    {
        switch ($column) {
            case 'last_reply':
                return $query->select("enquiries.*")
                    ->leftJoin('replies', function ($join) {
                        $join->on('replies.enquiry_id', '=', "enquiries.id");
                    })
                    ->groupBy("enquiries.id")
                    ->orderBy(DB::raw('replies.created_at IS NULL'), 'desc')
                    ->orderBy(DB::raw('replies.created_at'), $direction ?? 'asc');
                break;

            default:
                return $query->orderBy($column ?: 'created_at', $direction ?? 'asc');
                break;
        }
    }

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            if (empty($model->status)) {
                $model->status = AppStatus::PENDING->value;
            }
            if (empty($model->email)) {
                $model->email = optional(currentUser())->email;
            }
        });
    }
}
