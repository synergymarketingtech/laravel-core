<?php

namespace App\Traits;

use App\Models\Booking;
use App\Events\BookingCanceled;
use App\Events\BookingCreated;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Bookingable
{
    /**
     * Get all of the bookings for the ClassSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }

    /**
     * Get all of the active_bookings for the ClassSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function active_bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'schedule_id')->onlyActive();
    }

    /**
     * Get all of the stand_by_bookings for the ClassSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stand_by_bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'schedule_id')->onlyStandBy();
    }

    public function syncActiveBookings(Collection $active_bookings)
    {
        // delete removed active bookings
        $this->active_bookings()->whereNotIn('id', $active_bookings->pluck('id')->filter())->each(function ($booking) {
            $booking->update([
                'canceled_at' => now()
            ]);
            // send booking canceled notification
            event(new BookingCanceled($booking));
        });

        // create or updated new active bookings
        $active_bookings->filter(function ($item) {
            return isset($item['user']['id']);
        })->map(function ($item) {
            return (object) $item;
        })->each(function ($item) {
            $booking = $this->active_bookings()->updateOrCreate([
                'id' => optional($item)->id,
            ], [
                'is_stand_by' => false,
                'attendence' => optional($item)->attendence,
                'status' => optional($item)->status,
                'source' => optional($item)->source,
                'canceled_at' => null,
                'user_id' => optional($item)->user ? optional($item)->user['id'] : null,
            ]);

            if ($booking->wasRecentlyCreated) {
                // send booking confirm notification
                event(new BookingCreated($booking, $item->is_stand_by));
            }
        });

        return $this;
    }

    public function syncStandbyBookings(Collection $stand_by_bookings)
    {
        // delete removed active bookings
        $this->stand_by_bookings()->whereNotIn('id', $stand_by_bookings->pluck('id')->filter())->each(function ($booking) {
            $booking->update([
                'canceled_at' => now()
            ]);
            // send booking canceled notification
            event(new BookingCanceled($booking));
        });

        // create or updated new active bookings
        $stand_by_bookings->filter(function ($item) {
            return isset($item['user']['id']);
        })->map(function ($item) {
            return (object) $item;
        })->each(function ($item) {
            $booking = $this->stand_by_bookings()->updateOrCreate([
                'id' => optional($item)->id,
            ], [
                'is_stand_by' => $this->isStandby(),
                'attendence' => optional($item)->attendence,
                'status' => optional($item)->status,
                'source' => optional($item)->source,
                'canceled_at' => null,
                'user_id' => optional($item)->user ? optional($item)->user['id'] : null,
            ]);

            if ($booking->wasRecentlyCreated) {
                // send booking confirm notification
                event(new BookingCreated($booking));
            } else if ($booking->wasChanged('is_stand_by')) {
                event(new BookingCreated($booking, !$booking->is_stand_by));
            }
        });

        return $this;
    }

    public function updateStandbyBookings()
    {
        $this->stand_by_bookings()->each(function ($item) {
            if (!$this->isStandby()) {
                $booking = $item->update([
                    'is_stand_by' => false,
                ]);

                if ($booking) {
                    event(new BookingCreated($item, true));
                }
            }
        });

        return $this;
    }
}
