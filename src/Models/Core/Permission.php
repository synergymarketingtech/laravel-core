<?php

namespace Coderstm\Core\Models\Core;

use Coderstm\Core\Models\Core\Module;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'module_id',
        'action',
        'description',
        'scope',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the parent permissionable model.
     */
    public function permissionable()
    {
        return $this->morphTo();
    }
}
