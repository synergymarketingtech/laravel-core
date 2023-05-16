<?php

namespace Coderstm\Core\Models;

use Coderstm\Core\Traits\Core;
use Illuminate\Database\Eloquent\Model;

class ClassList extends Model
{
    use Core;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'capacity',
        'description',
        'urls',
        'extra',
        'is_active',
        'has_description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'urls' => 'collection',
        'is_active' => 'boolean',
        'has_description' => 'boolean',
        'extra' => 'boolean',
        'created_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * Get the description
     *
     * @param  string  $value
     * @return string
     */
    public function getDescriptionAttribute($value)
    {
        return $this->has_description || is_admin() ? $value : '';
    }
}
