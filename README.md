# Delivery Tracking

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/630d6d84-14d4-439d-ba30-0cb0b84f8d01/big.png)](https://insight.sensiolabs.com/projects/630d6d84-14d4-439d-ba30-0cb0b84f8d01)

This is a framework agnostic delivery tracking library for PHP 5.4+.
It uses the Adapter design pattern to provide a unified api over delivery services, and a common list of delivery statuses.
This library respects PSR-1, PSR-2, and PSR-4. 

## Install

Via Composer

``` bash
$ composer require lwiesel/delivery-tracking
```

## Usage

``` php
$chronopostAdapter = new ChronopostAdapter();
$deliveryTracking = new DeliveryTracking($chronopostAdapter);

$status = $deliveryTracking->getDeliveryStatus('tracking-number');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any **security related** issues, please email wiesel.laurent@gmail.com instead of using the issue tracker.

## Credits

- [Laurent Wiesel][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/lwiesel/delivery-tracking.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/lwiesel/delivery-tracking/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/lwiesel/delivery-tracking.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/lwiesel/delivery-tracking.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/lwiesel/delivery-tracking.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/lwiesel/delivery-tracking
[link-travis]: https://travis-ci.org/lwiesel/delivery-tracking
[link-scrutinizer]: https://scrutinizer-ci.com/g/lwiesel/delivery-tracking/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/lwiesel/delivery-tracking
[link-downloads]: https://packagist.org/packages/lwiesel/delivery-tracking
[link-author]: https://github.com/lwiesel
[link-contributors]: ../../contributors
