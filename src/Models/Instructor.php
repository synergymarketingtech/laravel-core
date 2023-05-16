<?php

namespace Coderstm\Core\Models;

use Coderstm\Core\Enum\AppStatus;
use Coderstm\Core\Traits\Core;
use Coderstm\Core\Traits\Fileable;
use Coderstm\Core\Models\File;
use Coderstm\Core\Traits\HasMorphToOne;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Instructor extends Model
{
    use Core, Fileable, HasMorphToOne;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'description',
        'urls',
        'insurance',
        'qualification',
        'document',
        'is_pt',
        'hourspw',
        'rentpw',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'urls' => 'collection',
        'insurance' => 'boolean',
        'qualification' => 'boolean',
        'document' => 'boolean',
        'is_pt' => 'boolean',
        'created_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'avatar',
    ];

    /**
     * The classes that belong to the Instructor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassList::class, 'instructor_class_lists', 'instructor_id', 'class_id')->onlyActive()->withPivot('cost');
    }

    public function syncClasses(Collection $classes, bool $detach = true)
    {
        $classes = $classes->filter(function ($class) {
            return isset($class['pivot']['cost']) && $class['pivot']['cost'] > 0;
        })->mapWithKeys(function ($class) {
            return [$class['id'] => [
                'cost' => $class['pivot']['cost'],
            ]];
        });
        if ($detach) {
            $this->classes()->sync($classes);
        } else {
            $this->classes()->syncWithoutDetaching($classes);
        }
        return $this;
    }

    public function syncClassesDetaching(Collection $classes)
    {
        return $this->syncClasses($classes, false);
    }

    public function insurance_file()
    {
        return $this->morphToOne(File::class, 'fileable')->wherePivot('type', 'insurance');
    }

    public function qualification_file()
    {
        return $this->morphToOne(File::class, 'fileable')->wherePivot('type', 'qualification');
    }

    public function document_file()
    {
        return $this->morphToOne(File::class, 'fileable')->wherePivot('type', 'document');
    }

    /**
     * Scope a query to only include onlyActive
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyActive($query)
    {
        return $query->whereStatus(AppStatus::ACTIVE);
    }
}
