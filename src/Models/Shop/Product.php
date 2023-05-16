<?php

namespace Coderstm\Core\Models\Shop;

use Coderstm\Core\Traits\Core;
use Coderstm\Core\Traits\Fileable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Core, HasSlug, Fileable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'has_size',
        'sizes',
        'price',
        'is_active',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'media',
        'thumbnail',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'has_size' => 'boolean',
        'is_active' => 'boolean',
        'sizes' => 'array',
        'created_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'size',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }

    /**
     * Scope a query to only include sort by
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBy($query, $column = 'CREATED_AT_ASC', $direction = 'asc')
    {
        switch ($column) {
            case 'CREATED_AT_ASC':
                $query->orderBy('created_at', 'asc');
                break;

            case 'CREATED_AT_DESC':
                $query->orderBy('created_at', 'desc');
                break;

            case 'PRICE_ASC':
                $query->orderBy('price', 'asc');
                break;

            case 'PRICE_DESC':
                $query->orderBy('price', 'desc');
                break;

            default:
                $query->orderBy($column ?: 'created_at', $direction ?? 'asc');
                break;
        }

        return $query;
    }

    public function getSizeAttribute()
    {
        return !empty($this->sizes) && count($this->sizes) ? $this->sizes[0] : null;
    }
}
