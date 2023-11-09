<?php

namespace App\Traits;

trait StaticCreateSelf
{
    public static function create(array $values): self
    {
        $dto = new self();

        foreach ($values as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->{$key} = $value ?? null;
            }
        }

        return $dto;
    }
}
