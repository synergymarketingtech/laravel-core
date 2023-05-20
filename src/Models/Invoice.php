<?php

namespace Coderstm\Models;

use Coderstm\Traits\Core;
use Laravel\Cashier\Cashier;
use Coderstm\Models\Invoice\LineItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Invoice as CashierInvoice;
use Stripe\Invoice as StripeInvoice;

class Invoice extends Model
{
    use Core;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'currency',
        'total',
        'stripe_status',
        'stripe_id',
        'note',
        'due_date',
        'subscription_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['amount', 'status', 'date'];

    /**
     * Get the subscription that owns the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Cashier::$subscriptionModel);
    }

    /**
     * Get all of the lines for the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines(): HasMany
    {
        return $this->hasMany(LineItem::class);
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @return string
     */
    protected function formatAmount($amount)
    {
        return Cashier::formatAmount($amount, $this->currency);
    }

    /**
     * Get the total amount that was paid (or will be paid).
     *
     * @return string
     */
    public function formatTotal()
    {
        return $this->formatAmount($this->total);
    }

    /**
     * Get the amount
     *
     * @return string
     */
    public function getAmountAttribute()
    {
        return $this->formatTotal();
    }

    /**
     * Get the status
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        return $this->stripe_status;
    }

    /**
     * Get the date
     *
     * @return string
     */
    public function getDateAttribute()
    {
        return $this->created_at->toFormattedDateString();
    }

    public static function createFromStripe(CashierInvoice $cashierInvoice, array $attributes = [])
    {
        $invoice = self::updateOrCreate([
            'stripe_id' => $cashierInvoice->id
        ], array_merge([
            'currency' => $cashierInvoice->currency,
            'total' => $cashierInvoice->rawRealTotal(),
            'stripe_status' => $cashierInvoice->status,
            'number' => $cashierInvoice->number,
            'due_date' => $cashierInvoice->dueDate(),
            'created_at' => $cashierInvoice->date(),
        ], $attributes));

        $invoiceLineItems = $cashierInvoice->invoiceLineItems();
        $invoice->lines()->whereNotIn('stripe_id', collect($invoiceLineItems)->pluck('id')->toArray())->delete();
        foreach ($invoiceLineItems as $item) {
            $invoice->lines()->updateOrCreate([
                'stripe_id' => $item->id
            ], [
                'description' => $item->description ?? null,
                'stripe_price' => $item->price->id ?? null,
                'stripe_plan' => $item->plan->id ?? null,
                'quantity' => $item->quantity ?? 1,
                'amount' => $item->amount ?? 0,
                'currency' => $item->currency,
            ]);
        }

        return $invoice->fresh(['lines']);
    }

    /**
     * Scope a query to only include open
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query)
    {
        return $query->where('stripe_status', StripeInvoice::STATUS_OPEN);
    }
}
