<?php

declare(strict_types=1);

namespace Tests\Roelofr\EncryptionCast\Stubs\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Arr;
use Tests\Roelofr\EncryptionCast\Stubs\Models\Address;

// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
class AddressCast implements CastsAttributes
{
    /**
     * @inheritdoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        try {
            $values = \json_decode($value, true, 6, \JSON_THROW_ON_ERROR);
            return new Address(
                Arr::get($values, 'line1'),
                Arr::get($values, 'line2'),
                Arr::get($values, 'city'),
                Arr::get($values, 'postcode'),
                Arr::get($values, 'country')
            );
        } catch (\JsonException $exception) {
            dd($value, $exception);
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof Address) {
            return json_encode($value->toArray());
        }
        return null;
    }
}
