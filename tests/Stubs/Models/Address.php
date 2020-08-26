<?php

declare(strict_types=1);

namespace Tests\Roelofr\EncryptionCast\Stubs\Models;

class Address
{
    public ?string $line1 = null;
    public ?string $line2 = null;
    public ?string $city = null;
    public ?string $postcode = null;
    public ?string $country = null;

    /**
     * Makes a new Address
     * @param null|string $line1
     * @param null|string $line2
     * @param null|string $city
     * @param null|string $postcode
     * @param null|string $country
     */
    public function __construct(
        ?string $line1 = null,
        ?string $line2 = null,
        ?string $city = null,
        ?string $postcode = null,
        ?string $country = null
    ) {
        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->city = $city;
        $this->postcode = $postcode;
        $this->country = $country;
    }

    /**
     * Returns Address as array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'country' => $this->country,
        ];
    }

    /**
     * Returns address as a formatted address
     * @return string
     */
    public function __toString(): string
    {
        // Remove
        $address = <<<TEXT
        {$this->line1}
        {$this->line2}
        {$this->postcode} {$this->city}
        {$this->country}
        TEXT;

        // Remove excess newlines
        return trim(str_replace(\PHP_EOL . \PHP_EOL, \PHP_EOL, $address));
    }
}
