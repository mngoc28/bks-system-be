<?php

namespace App\Traits;

trait EnumTrait
{
    public function equals($value): bool
    {
        return $this->value == $value;
    }
}
