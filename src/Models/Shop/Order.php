<?php

namespace Coderstm\Core\Models\Shop;

use Coderstm\Core\Models\User;
use Coderstm\Core\Traits\Core;
use Coderstm\Core\Models\Shop\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use Core;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'status',
        'total',
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
     * Get the user that owns the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The line_items that belong to the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function line_items(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_order', 'order_id', 'product_id')->withPivot([
            'size',
            'price',
            'quantity',
        ]);
    }
}
