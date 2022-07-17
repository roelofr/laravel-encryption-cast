# Encryption Cast for Laravel

[![Build status][shield-build]][link-build]
[![Code Climate maintainability rating][shield-cc-maintainability]][link-cc-maintainability]
[![Code Climate coverage rating][shield-cc-coverage]][link-cc-coverage]


[![PHP code style: PSR-12][shield-php]][link-php]
[![License: MIT][shield-license]][link-license]

A super-simple, fully tested database encryption system, which allows for extra
conversions to take place afterwards.

## End of Life

This package has reached it's end of life. [Laravel now supports encryption out-of-the-box][eol],
so adding a separate package that does the same is useless.

[eol]: https://laravel.com/docs/9.x/eloquent-mutators#encrypted-casting

## License

The software is licensed under the [MIT License][link-license].

## Requirements

This project requires Laravel 7.x and PHP 7.4 or newer. You should also update
your database schemas to allow for encrypted data. Encrypted data is
base64-encoded, and might be significantly longer than the same data as, for
example, JSON.

It's recommended to use long `VARCHAR` fields or even `TEXT` fields.

## Installation

Just require it via composer, it doesn't register a service provider.

```
composer require roelofr/laravel-encryption-cast
```

## Usage

This class contains a two-way cast that takes extra casts as first and only
argument. `null`-values are not encrypted.

### Encrypted strings

To encrypt the `phone_number` field, simply cast it.

```php
protected $casts = [
    'phone_number' => \Roelofr\EncryptionCast\Casts\EncryptedAttribute::class
];
```

### Encrypted basic types

Say we have a `date_of_birth` field, we can cast that as a date.

```php
protected $casts = [
    'date_of_birth' => \Roelofr\EncryptionCast\Casts\EncryptedAttribute::class . ':date'
];
```

### Encrypted collections

Now, say we have an `address` which is a collection, we can also cast it like
that.

```php
protected $casts = [
    'address' => \Roelofr\EncryptionCast\Casts\EncryptedAttribute::class . ':collection'
];
```

### Encrypted complex models

Now, lastly, say you made a [custom cast][docs-custom-casts] that casts the
`medication` field to some other type, and named it `App\Casts\MedicationCast`,
you can send that as a second argument.

```php
protected $casts = [
    'medication' => \Roelofr\EncryptionCast\Casts\EncryptedAttribute::class . ':' . \App\Casts\MedicationCast::class
];
```

And that's about it.

## Compatibility

To ease development you can use one of the casts in the `Compat` namespace.
These are included:

- For [`austinheap/laravel-database-encryption`][compat-1] you can use
  `AustinHeapEncryptedAttribute`.

[compat-1]: https://github.com/austinheap/laravel-database-encryption

## Contributing

If you found any bugs or issues and can help, please open an issue. The code is
linted for code style and coverage is exected to be >95% at all times.

<!-- Links -->

[shield-build]: https://img.shields.io/github/workflow/status/roelofr/laravel-encryption-cast/Run%20unit%20tests.svg?style=flat
[shield-cc-maintainability]: https://img.shields.io/codeclimate/maintainability/roelofr/laravel-encryption-cast.svg?label=CodeClimate+Maintainability&style=flat
[shield-cc-coverage]: https://img.shields.io/codeclimate/coverage-letter/roelofr/laravel-encryption-cast.svg?style=flat
[shield-php]: https://img.shields.io/badge/php%20code%20style-PSR--12-8892be.svg?style=flat
[shield-license]: https://img.shields.io/github/license/roelofr/laravel-encryption-cast.svg?style=flat

[link-build]: https://github.com/roelofr/laravel-encryption-cast/actions
[link-cc-maintainability]: https://codeclimate.com/github/roelofr/laravel-encryption-cast
[link-cc-coverage]: https://codeclimate.com/github/roelofr/laravel-encryption-cast
[link-php]: https://www.php-fig.org/psr/psr-12/
[link-license]: LICENSE.md

[docs-custom-casts]: https://laravel.com/docs/7.x/eloquent-mutators#custom-casts
