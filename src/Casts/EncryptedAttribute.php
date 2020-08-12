<?php

declare(strict_types=1);

namespace Roelofr\EncryptionCast\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Roelofr\EncryptionCast\Helpers\Caster;

class EncryptedAttribute implements CastsAttributes
{
    /**
     * The type for coercion.
     */
    protected ?string $type;

    /**
     * Caster that casts to and from the database
     */
    protected ?Caster $caster = null;

    /**
     * Create a new cast class instance.
     * @param string|null $type
     */
    public function __construct(?string $type = null)
    {
        $this->type = $type === 'null' ? null : $type;

        if ($this->type) {
            $this->caster = new Caster($type);
        }
    }

    /**
     * Cast the given value.
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function get($model, $key, $value, $attributes)
    {
        // Decrypt if set
        if ($value !== null) {
                $value = $this->decryptValue($value);
        }

        // Return as-is if no type casting is to take place
        if (!$this->caster) {
            return $value;
        }

        // Update caster to work properly
        $this->caster->setModel($model);

        // Cast from table
        return $this->caster->castFromDatabase($value);
    }

    /**
     * Prepare the given value for storage.
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function set($model, $key, $value, $attributes)
    {
        // SKip if null
        if ($value === null) {
            return null;
        }

        // Update via caster
        if ($this->caster) {
            $this->caster->setModel($model);

            // Convert to string value
            $value = $this->caster->castToDatabase($value);
        }

        // Encrypt value
        return Crypt::encryptString($value);
    }

    /**
     * Handles a failed decryption
     * @param string $value
     * @return null|string
     */
    protected function handleFailedDecryption(string $value): ?string
    {
        return $value;
    }

    /**
     * Runs a safe decryption of a string, using normal forms or legacy forms if using that old encryption form
     * @param string $value
     * @return null|string
     */
    private function decryptValue(string $value): ?string
    {
        try {
            // Try a plain decrypt
            return Crypt::decryptString($value);
        } catch (DecryptException $exception) {
            // Report failure
            \report($exception);

            // Forward to fallback
            return $this->handleFailedDecryption($value);
        }
    }
}
