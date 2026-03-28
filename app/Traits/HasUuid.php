<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUuid
{
    protected static function boot()
    {
        parent::boot();
        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return "string";
    }
}
