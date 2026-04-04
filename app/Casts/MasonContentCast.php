<?php

namespace App\Casts;

use App\Support\MasonContent;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use JsonException;

class MasonContentCast implements CastsAttributes
{
    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function get($model, string $key, $value, array $attributes): ?array
    {
        return MasonContent::normalize($value);
    }

    /**
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        return MasonContent::encode($value);
    }
}
