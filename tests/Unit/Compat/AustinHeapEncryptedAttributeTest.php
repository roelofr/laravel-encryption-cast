<?php

declare(strict_types=1);

namespace Tests\Roelofr\EncryptionCast\Unit\Casts\Compat;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\App;
use Roelofr\EncryptionCast\Casts\Compat\AustinHeapEncryptedAttribute;
use Roelofr\EncryptionCast\Casts\EncryptedAttribute;
use Tests\Roelofr\EncryptionCast\Models\Dummy;
use Tests\Roelofr\EncryptionCast\TestCase;

/**
 * Tests encryption cast
 * @package Tests\Unit\Casts
 */
class AustinHeapEncryptedAttributeTest extends TestCase
{
    /**
     * Tests all scenarios
     * @param string $input
     * @param string $type
     * @param mixed $result
     * @return void
     * @dataProvider provideTestRows
     */
    public function testBasicRetrieval($result, string $type, ?string $input)
    {
        // Get a random model
        $user = (new Dummy())
            ->setCast(AustinHeapEncryptedAttribute::class . ':' . $type)
            ->setRawAttribute($input);

        // Cast
        $casted = $user->test;

        // Check scalar types
        if (!\is_object($result)) {
            $this->assertSame($result, $casted);
            return;
        }

        // Check objects
        $this->assertInstanceOf(\get_class($result), $casted);
        $this->assertEquals($result, $casted);
    }

    /**
     * @dataProvider provideTestRows
     */
    public function testFullCircle($result, string $type)
    {
        // Get a random model
        $model = (new Dummy())
            ->setCast(AustinHeapEncryptedAttribute::class . ':' . $type);

        // Cast to DB
        $model->test = $result;
        $model->save();

        // Check without cast
        $compareModel = Dummy::find($model->id);
        if ($result === null) {
            $this->assertNull($compareModel->test);
        } else {
            $this->assertIsString($compareModel->test);
            $this->assertNotSame($result, $compareModel->test);
        }

        // Re-check with cast that doesn't support AusinHeap's encryption header
        $model = Dummy::find($model->id)
            ->setCast(EncryptedAttribute::class . ':' . $type);

        // Check non-objects
        if (!\is_object($result)) {
            $this->assertSame($result, $model->test);
            return;
        }

        // Check objects
        $this->assertEquals($result, $model->test);
    }

    /**
     * @dataProvider malformedDataProvider
     */
    public function testMalformedData(string $input)
    {
        // Get caster
        $caster = new AustinHeapEncryptedAttribute('string');

        // Get a random model
        $user = new Dummy();

        // Mock an exception handler
        $handler = $this->createMock(ExceptionHandler::class);

        // Mock the report method
        $handler->expects($this->atLeastOnce())
            ->method('report')
            ->with($this->isInstanceOf(DecryptException::class))
            ->willReturn(null);

        // Replace exception handler
        App::instance(ExceptionHandler::class, $handler);

        // Ensure a null value
        $this->assertSame($input, $caster->get($user, 'test', $input, []));
    }

    /**
     * Tests decrypting data
     * @return array<string>
     */
    public function provideTestRows(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'null' => [
                null,
                'null',
                null
            ],
            'string' => [
                'Hello World!',
                'string',
                'eyJpdiI6IlNOZUNKUlJWMVNjRkYxTU5rdUJrYUE9PSIsInZhbHVlIjoiRHJIbXcxdFkxYmhRRndzZnRTSkRNdz09IiwibWFjIjoiZWVhOGE1YzEzMjU1YTE2ZmUyNTUyMGU1MjcwY2VmYjg4ZDU0NDZjOThiYjYxYjg3YTY3MWRjMjUwNDBhZDQ1OCJ9'
            ],
            'legacy string' => [
                'Hello World Too!',
                'string',
                // unreadables are 0x0001, 0x0002 and other separators
                '__LARAVEL-DATABASE-ENCRYPTED-VERSION-00-01-02__versionVERSION-00-01-02typestring[native]eyJpdiI6IlNMdWVoaFRvVWE1bnhFM2Vna1YwQ3c9PSIsInZhbHVlIjoielFkblQ0Q1AyUDN3SFhVY0taYkt5Yks4VzNBNitscGp1STd3MGhaMGxzUT0iLCJtYWMiOiIxMDczNjIwNmU3YTdmY2IwZWI2ZDhlZjdjNDdiZTI5NzNlMmY1YWIzZTk2Y2U0N2I5ZTA2ODQyMzI1NGE1NTllIn0=',
            ]
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    /**
     * Returns a set of malformed data
     * @return array<string>
     */
    public function malformedDataProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'normal' => ['eyJpdiI6IjlRWDVhbXBHTkV6K3ZpVFZmM1dMYXc9PSIsInZhbHVlIjoidk1nYVQzUVlyUmI0ZjYzYjBjZWIwY2I3NWZhZjBmNGM3YzBhYmIzYTFlNDg2NzA2MWY2OWIyMDM3MGQ2ZTFmMzkyOSJ9'],
            'legacy (valid header)' => ['__LARAVEL-DATABASE-ENCRYPTED-VERSION-00-01-02__versionVERSION-00-01-02typestring[native]eyJpdiI6IkxONzVvMHVyT1pTQzB3MVZ4ZVNycFE9PSIsInZhbHVlIjoib3M1dDJxVXptekdVSUdIS3RBZjFpQT09'],
            'legacy (invalid header)' => ['__LARAVEL-CUSTOM_VALUE-VERSION-00-01-02__versionVERSION-00-01-02typestring[native]eyJpdiI6IkxONzVvMHVyT1pTQzB3MVZ4ZVNycFE9PSIsInZhbHVlIjoib3M1dDJxVXptekdVSUdIS3RBZjFpQT09']
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
