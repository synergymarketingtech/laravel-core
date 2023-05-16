<?php

namespace Coderstm\Core\Models\Core;

use Coderstm\Core\Models\Core\Permission;
use Coderstm\Core\Traits\Core;
use Coderstm\Core\Traits\HasPermission;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use Core, HasPermission;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'permissions',
    ];

    /**
     * Get the parent groupable model.
     */
    public function groupable()
    {
        return $this->morphTo();
    }
}
