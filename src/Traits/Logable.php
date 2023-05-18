<?php

namespace Coderstm\Traits;

use Coderstm\Enum\LogType;
use Coderstm\Models\Log;
use Coderstm\Models\Plan;
use Illuminate\Support\Str;

trait Logable
{
    public function logs()
    {
        return $this->morphMany(Log::class, 'logable')->orderBy('created_at', 'desc');
    }

    public function getDisplayableAttribute($attribute, $attributes = [])
    {
        if (isset($attributes[$attribute])) {
            return $attributes[$attribute];
        }
        return str_replace('_', ' ', Str::snake($attribute));
    }

    private static function getLogValue($key, $value)
    {
        switch ($key) {
            case 'plan_id':
                return optional(Plan::find($value))->label;
                break;

            default:
                return $value;
                break;
        }
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            $modelName = class_basename(get_class($model));
            $data = [
                'message' => "{$modelName} has been created.",
            ];
            if (!empty($model->log_options)) {
                $data['options'] = $model->log_options;
            }
            $model->logs()->updateOrCreate([
                'type' => LogType::CREATED,
            ], $data);
        });
        static::deleted(function ($model) {
            $modelName = class_basename(get_class($model));
            $model->logs()->create([
                'type' => LogType::DELETED,
                'message' => "{$modelName} has been deleted.",
            ]);
        });
        static::updated(function ($model) {
            $modelName = class_basename(get_class($model));
            $options = [];
            foreach ($model->getFillable() as $key) {
                if ($model->wasChanged($key)) {
                    $options[$key] = [
                        'previous' => static::getLogValue($key, $model->getOriginal($key)),
                        'current' => static::getLogValue($key, $model[$key]),
                    ];
                }
            }
            $model->logs()->create([
                'type' => LogType::UPDATED,
                'message' => "{$modelName} has been updated.",
                'options' => $options
            ]);
        });
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $modelName = class_basename(get_class($model));
                $model->logs()->create([
                    'type' => LogType::RESTORED,
                    'message' => "{$modelName} has been restored.",
                ]);
            });
        }
    }
}
