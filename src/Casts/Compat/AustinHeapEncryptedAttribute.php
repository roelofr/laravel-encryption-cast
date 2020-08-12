<?php

declare(strict_types=1);

namespace Roelofr\EncryptionCast\Casts\Compat;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Roelofr\EncryptionCast\Casts\EncryptedAttribute;

class AustinHeapEncryptedAttribute extends EncryptedAttribute
{
    private const OLD_ENCRYPTION_HEADER_START = "\001";
    private const OLD_ENCRYPTION_START = "/^(?:.{0,2})\002__LARAVEL-DATABASE-ENCRYPTED-(?:.+?)__\003/";
    private const OLD_ENCRYPTION_HEADER_END = "\004";

    /**
     * Get decryptable part of old form
     * @param string $value
     * @return string
     */
    public function getOldEncryptedPart(string $value): string
    {
        return Str::afterLast($value, self::OLD_ENCRYPTION_HEADER_END);
    }

    /**
     * Handles a failed decryption by trying the decryption written by AustinHeap
     * @param string $value
     * @return null|string
     */
    protected function handleFailedDecryption(string $value): ?string
    {
        // Check for legacy encryption
        if (!$this->isOldEncryption($value)) {
            return $value;
        }

        try {
            $payload = $this->getOldEncryptedPart($value);
            return Crypt::decryptString($payload);
        } catch (DecryptException $exception) {
            // Report again
            \report($exception);

            // Return original
            return $value;
        }
    }
    /**
     * Decrypts data
     * @param string $value
     * @return void
     */
    private function isOldEncryption(string $value)
    {
        // Skip if no header is present
        if (!Str::startsWith($value, self::OLD_ENCRYPTION_HEADER_START)) {
            return false;
        }

        // Get header
        $header = Str::before($value, self::OLD_ENCRYPTION_HEADER_END);

        // Skip if header doesn't start right
        if (empty($header) || !\preg_match(self::OLD_ENCRYPTION_START, $header)) {
            return false;
        }

        // It's an old encryption
        return true;
    }
}
