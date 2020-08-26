<?php

declare(strict_types=1);

namespace Tests\Roelofr\EncryptionCast\Unit\Casts;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Roelofr\EncryptionCast\Casts\EncryptedAttribute;
use Tests\Roelofr\EncryptionCast\Stubs\Models\Dummy;
use Tests\Roelofr\EncryptionCast\TestCase;

/**
 * Tests encryption cast
 * @package Tests\Unit\Casts
 */
class EncryptedAttributeTest extends TestCase
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
            ->setCast(EncryptedAttribute::class . ':' . $type)
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
     * Tests some storage scenarios
     */
    public function testBasicStorage()
    {
        // Get caster
        $caster = new EncryptedAttribute('string');

        // Get a random model
        $user = new Dummy();

        // Test if null results in null, but non-null results in non-null
        $this->assertNull($caster->get($user, 'test', null, []));
        $this->assertIsString($caster->get($user, 'test', 'Hello World!', []));
    }

    /**
     * @dataProvider provideTestRows
     */
    public function testFullCircle($result, string $type)
    {
        // Get a random model
        $model = (new Dummy())
            ->setCast(EncryptedAttribute::class . ':' . $type);

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

        // Re-check with cast
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
        $caster = new EncryptedAttribute('string');

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
            'as-is' => [
                '55.004',
                'null',
                'eyJpdiI6IlE5eU9FRUdrMEE3YXVDUEVZeU5pbnc9PSIsInZhbHVlIjoiMHVyQkRuWEpNNlRnNEhsbFRuUDhndz09IiwibWFjIjoiMDA4YWI0OTAwZDQyYjQ2ZDg0MzdhMWVmYTliMDM0MmU2YjhkNjJlYWNiNWY1YzJmNmI4ZDY1NDczZDM5NjA1OCJ9'
            ],
            'float' => [
                55.004,
                'float',
                'eyJpdiI6ImpuY1U1VHpKMTJGR3BjTWFHYkt2MUE9PSIsInZhbHVlIjoidWxoQzJ5ZzBmRGdiN25KUEorU0xYZz09IiwibWFjIjoiMzE5NGE4NDNmMGYwYWU5OTA5NTIwYTM1MjEyYWY2MzNjMjY5MTEyM2ZhNzUyNThkNzFlNDlhYzY3Mzk5MjU2MCJ9'
            ],
            'date' => [
                Date::parse('2020-06-15'),
                'date',
                'eyJpdiI6IlAyOHQ3bzZKZmZmbEpjSWxxZUJBVFE9PSIsInZhbHVlIjoiTWkzR2JhdWxpK1RRbnJObDUzMDRwVDlUUmtqdFBSVnRqOFlhVk1xVWFVdz0iLCJtYWMiOiI2YTQzYTNjZDljNGE5NDUxNWYyMjU3ZDRlNTczMGMwYjgzNjYzZGJmNmJhY2RiZjIxZGExOWUwMTRiNWU1YWM5In0='
            ],
            'collection' => [
                collect(['a' => true, 'b' => 'banana']),
                'collection',
                'eyJpdiI6IlgrTCtmTlhBVlFjTm9mclhjRzE4Rnc9PSIsInZhbHVlIjoiVlN0cHJrZktpYkVubUE4SE81NGIxenpoV0dsL25sWmgreDBBN2NoM0xUVT0iLCJtYWMiOiIyOTM1ODBhNjYyNjcxNTg5MGQwOWU4MjAwNjAyNDU3MGU0OTQ1MDE3OTdkYjhiNTIxZGM1NzgzZGE1ZjQzMzlmIn0='
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
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
