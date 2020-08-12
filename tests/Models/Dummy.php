<?php

declare(strict_types=1);

namespace Tests\Roelofr\EncryptionCast\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Empty model with some helpers
 */
class Dummy extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'dummy_rows';

    /**
     * Sets the current cast to use
     * @param string $value
     * @return Dummy
     */
    public function setCast(string $value): self
    {
        // Replace cast
        $this->casts['test'] = $value;

        // Drop cache
        $this->dropCache();

        // Allow chaining
        return $this;
    }

    /**
     * Get the model's raw original attribute values.
     * @param  mixed  $value
     * @return self
     */
    public function setRawAttribute($value = null)
    {
        // Apply
        $this->attributes['test'] = $value;

        // Drop cache
        $this->dropCache();

        // Allow chaining
        return $this;
    }

    /**
     * Drops the cache
     * @return void
     */
    public function dropCache(): void
    {
        unset($this->classCastCache['test']);
    }
}
