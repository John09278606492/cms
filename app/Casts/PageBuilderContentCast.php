<?php

namespace App\Casts;

use App\Support\PageBuilderContent;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use JsonException;

class PageBuilderContentCast implements CastsAttributes
{
    /**
     * @return array<string, mixed>|null
     */
    public function get($model, string $key, $value, array $attributes): ?array
    {
        return PageBuilderContent::normalize($value);
    }

    /**
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        return PageBuilderContent::encode($value);
    }
}
