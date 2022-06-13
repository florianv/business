<?php

/*
 * This file is part of Business.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Business\Tests;

use Business\DateRange;
use Business\Holidays;
use PHPUnit\Framework\TestCase;

class HolidaysTest extends TestCase
{
    public function testIsHoliday()
    {
        $holidays = new Holidays(
            [
                $holiday = new \DateTime('2015-05-11'),
                new DateRange(new \DateTime('2015-07-08'), new \DateTime('2015-07-21')),
            ]
        );

        $this->assertTrue($holidays->isHoliday($holiday));
        $this->assertTrue($holidays->isHoliday(new \DateTime('2015-07-09 10:00')));
    }

    public function testAddHoliday()
    {
        $holiday = new \DateTime('2015-05-11');

        $holidays = new Holidays();
        $holidays->addHoliday($holiday);

        $this->assertTrue($holidays->isHoliday($holiday));
    }

    public function testAddHolidays()
    {
        $holiday = new \DateTime('2015-05-11');
        $holidayRange = new DateRange(new \DateTime('2015-07-08'), new \DateTime('2015-07-21'));

        $holidays = new Holidays();
        $holidays->addHolidays([$holiday]);
        $holidays->addHolidays($holidayRange);

        $this->assertTrue($holidays->isHoliday($holiday));
        $this->assertTrue($holidays->isHoliday(new \DateTime('2015-07-09 10:00')));
    }

    public function testSerializeUnserialize()
    {
        $holidays = new Holidays(
            [
                $holiday = new \DateTime('2015-05-11'),
                new DateRange(new \DateTime('2015-07-08'), new \DateTime('2015-07-21')),
            ]
        );

        $serialized = serialize($holidays);
        $unserialized = unserialize($serialized);

        $this->assertTrue($unserialized->isHoliday($holiday));
        $this->assertTrue($unserialized->isHoliday(new \DateTime('2015-07-09 10:00')));
    }

    public function testJsonSerialize()
    {
        $holidays = new Holidays(
            [
                $holiday = new \DateTime('2015-05-11'),
                new DateRange(new \DateTime('2015-07-08'), new \DateTime('2015-07-21')),
            ]
        );

        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/Expected/Holidays/testJsonSerialize.json',
            json_encode($holidays)
        );
    }
}
