<?php

declare(strict_types=1);

namespace Roelofr\EncryptionCast\Helpers;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Caster to let Laravel handle basic casting, from array and such
 */
class Caster
{
    use HasAttributes;

    /**
     * The type we're casting this value to
     * @var string
     */
    private string $castType;

    /**
     * Prep with cast type
     * @param string $type
     * @return void
     */
    public function __construct(string $type)
    {
        $this->castType = $type;
    }

    /**
     * Mutate value from decrypted string to value
     * @param null|string $value
     * @return mixed
     */
    public function castFromDatabase(?string $value)
    {
        return $this->castAttribute('key', $value);
    }

    /**
     * Converts a value to be storable in the database
     * @param mixed $value
     * @return null|string
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function castToDatabase($value): ?string
    {
        // If an attribute is listed as a "date", we'll convert it from a DateTime
        // instance into a form proper for storage on the database tables using
        // the connection grammar's date format. We will auto set the values.
        if ($value && $this->isDateAttribute('key')) {
            $value = $this->fromDateTime($value);
        }


        if ($this->isJsonCastable('key') && !is_null($value)) {
            $value = $this->castAttributeAsJson('key', $value);
        }

        // Done mutating
        return (string) $value;
    }

    /**
     * Scam the casts array for HasAttributes::castAttribute.
     * @return array
     */
    public function getCasts()
    {
        return ['key' => $this->castType];
    }

    /**
     * Sets the model, for date methods
     * @param Model $model
     * @return void
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * Get the format for dates from the model.
     * @return string
     */
    public function getDateFormat()
    {
        return $this->model->getDateFormat();
    }

    /**
     * Don't decrypt timestamps, since it's hardly ever used
     * @return false
     */
    public function usesTimestamps(): bool
    {
        return false;
    }
}
