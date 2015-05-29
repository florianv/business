# Business [![Build status][travis-image]][travis-url] [![Version][version-image]][version-url] [![PHP Version][php-version-image]][php-version-url]

> DateTime calculations in business hours

## Installation

```bash
$ composer require florianv/business
```

## Usage

First you need to configure your business schedule:

```php
use Business\SpecialDay;
use Business\Day;
use Business\Days;
use Business\Business;

// Opening hours for each week day. If not specified, it is considered closed
$days = [
    // Standard days with fixed opening hours
    new Day(Days::MONDAY, [['09:00', '13:00'], ['2pm', '5 PM']]),
    new Day(Days::TUESDAY, [['9 AM', '5 PM']]),
    new Day(Days::WEDNESDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
    new Day(Days::THURSDAY, [['10 AM', '5 PM']]),
    
    // Special day with dynamic opening hours depending on the date
    new SpecialDay(Days::FRIDAY, function (\DateTime $date) {
        if ('2015-05-29' === $date->format('Y-m-d')) {
            return [['9 AM', '12:00']];
        }
    
        return [['9 AM', '5 PM']];
    }),
];

// Optional holiday dates
$holidays = [new \DateTime('2015-01-01'), new \DateTime('2015-01-02')];

// Optional business timezone
$timezone = new \DateTimeZone('Europe/Paris');

// Create a new Business instance
$business = new Business($days, $holidays, $timezone);
```

### Methods

##### within() - Tells if a date is within business hours

```php
$bool = $business->within(new \DateTime('2015-05-11 10:00'));
```

##### timeline() - Returns a timeline of business dates

```php
$start = new \DateTime('2015-05-11 10:00');
$end = new \DateTime('2015-05-14 10:00');
$interval = new \DateInterval('P1D');

$dates = $business->timeline($start, $end, $interval);
```

##### closest() - Returns the closest business date from a given date

```php
// After that date (including it)
$nextDate = $business->closest(new \DateTime('2015-05-11 10:00'));

// Before that date (including it)
$lastDate = $business->closest(new \DateTime('2015-05-11 10:00'), Business::CLOSEST_LAST);
```

## License

[MIT](https://github.com/florianv/business/blob/master/LICENSE)

[travis-url]: https://travis-ci.org/florianv/business
[travis-image]: http://img.shields.io/travis/florianv/business.svg?style=flat

[version-url]: https://packagist.org/packages/florianv/business
[version-image]: http://img.shields.io/packagist/v/florianv/business.svg?style=flat

[php-version-url]: https://packagist.org/packages/florianv/business
[php-version-image]: http://img.shields.io/badge/php-5.4+-ff69b4.svg
