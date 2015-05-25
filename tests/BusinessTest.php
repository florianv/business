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

use Business\Business;
use Business\Days;
use Business\Day;

class BusinessTest extends \PHPUnit_Framework_TestCase
{
    public function testWithin()
    {
        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ]);

        $this->assertTrue($business->within(new \DateTime('2015-05-11 10:00'))); // Monday
        $this->assertTrue($business->within(new \DateTime('2015-05-11 17:00')));

        $this->assertFalse($business->within(new \DateTime('2015-05-11 18:00'))); // Monday
        $this->assertFalse($business->within(new \DateTime('2015-05-12 10:00'))); // Tuesday
    }

    public function testWithinWithHoliday()
    {
        $holiday = new \DateTime('2015-05-11'); // Monday

        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
        ], [$holiday]);

        $this->assertFalse($business->within($holiday));
        $this->assertTrue($business->within(new \DateTime('2015-05-18 10:00')));
    }

    public function testWithinCustomTimezone()
    {
        $tz = date_default_timezone_get();
        date_default_timezone_set('Europe/Paris');

        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ]);

        // "2015-05-25 22:00:00" in Europe/Paris
        $date = new \DateTime('2015-05-25 10:00', new \DateTimeZone('Pacific/Tahiti'));

        $this->assertFalse($business->within($date));

        date_default_timezone_set($tz);
    }

    public function testClosestBefore()
    {
        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ]);

        // Withing working hours
        $target = new \DateTime('2015-05-11 09:00'); // Monday
        $date = $business->closest($target, Business::CLOSEST_LAST);
        $this->assertEquals('2015-05-11 09:00:00', $date->format('Y-m-d H:i:s')); // Monday

        // The last day
        $target = new \DateTime('2015-05-12 08:00'); // Tuesday
        $date = $business->closest($target, Business::CLOSEST_LAST);
        $this->assertEquals('2015-05-11 17:00:00', $date->format('Y-m-d H:i:s')); // Monday

        // Last week
        $target = new \DateTime('2015-05-11 08:00'); // Monday
        $date = $business->closest($target, Business::CLOSEST_LAST);
        $this->assertEquals('2015-05-08 17:00:00', $date->format('Y-m-d H:i:s')); // Last Friday
    }

    public function testClosestBeforeWithHolidays()
    {
        $target = new \DateTime('2015-05-11 08:00'); // Monday
        $holidayOne = new \DateTime('2015-05-08'); // Friday
        $holidayTwo = new \DateTime('2015-05-04'); // Monday

        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ], [$holidayOne, $holidayTwo]);

        $date = $business->closest($target, Business::CLOSEST_LAST);
        $this->assertEquals('2015-05-01 17:00:00', $date->format('Y-m-d H:i:s')); // Last Friday
    }

    public function testClosestBeforeFirstDayHoliday()
    {
        $target = new \DateTime('2015-05-11 10:00'); // Monday
        $holiday = new \DateTime('2015-05-11'); // Monday

        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
        ], [$holiday]);

        $date = $business->closest($target, Business::CLOSEST_LAST);

        $this->assertEquals('2015-05-04 17:00:00', $date->format('Y-m-d H:i:s')); // Last 2 Monday
    }

    public function testClosestBeforeCustomTimezone()
    {
        $tz = date_default_timezone_get();
        date_default_timezone_set('Europe/Paris');

        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ]);

        // Monday "2015-05-25 22:00:00" in Europe/Paris
        $date = new \DateTime('2015-05-25 10:00', new \DateTimeZone('Pacific/Tahiti'));
        $closest = $business->closest($date, Business::CLOSEST_LAST);

        $this->assertEquals('2015-05-25 17:00', $closest->format('Y-m-d H:i'));

        date_default_timezone_set($tz);
    }

    public function testClosestAfter()
    {
        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ]);

        // Withing working hours
        $target = new \DateTime('2015-05-11 10:00'); // Monday
        $date = $business->closest($target, Business::CLOSEST_NEXT);
        $this->assertEquals('2015-05-11 10:00:00', $date->format('Y-m-d H:i:s')); // Monday

        // The next day
        $target = new \DateTime('2015-05-12 17:30'); // Tuesday
        $date = $business->closest($target, Business::CLOSEST_NEXT);
        $this->assertEquals('2015-05-15 10:00:00', $date->format('Y-m-d H:i:s')); // Friday

        // Next week
        $target = new \DateTime('2015-05-15 17:30'); // Friday
        $date = $business->closest($target, Business::CLOSEST_NEXT);
        $this->assertEquals('2015-05-18 09:00:00', $date->format('Y-m-d H:i:s')); // Next Monday
    }

    public function testClosestAfterWithHolidays()
    {
        $target = new \DateTime('2015-05-11 17:15'); // Monday
        $holidayOne = new \DateTime('2015-05-15'); // Friday
        $holidayTwo = new \DateTime('2015-05-18'); // Monday

        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ], [$holidayOne, $holidayTwo]);

        $date = $business->closest($target, Business::CLOSEST_NEXT);
        $this->assertEquals('2015-05-22 10:00:00', $date->format('Y-m-d H:i:s')); // Next Friday
    }

    public function testClosestAfterFirstDayHoliday()
    {
        $target = new \DateTime('2015-05-11 10:00'); // Monday
        $holiday = new \DateTime('2015-05-11'); // Monday

        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
        ], [$holiday]);

        $date = $business->closest($target, Business::CLOSEST_NEXT);

        $this->assertEquals('2015-05-18 09:00:00', $date->format('Y-m-d H:i:s')); // Last 2 Monday
    }

    public function testClosestAfterCustomTimezone()
    {
        $tz = date_default_timezone_get();
        date_default_timezone_set('Europe/Paris');

        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ]);

        // Monday "2015-05-25 22:00:00" in Europe/Paris
        $date = new \DateTime('2015-05-25 10:00', new \DateTimeZone('Pacific/Tahiti'));
        $closest = $business->closest($date, Business::CLOSEST_NEXT);

        $this->assertEquals('2015-05-29 10:00', $closest->format('Y-m-d H:i')); // Next Friday

        date_default_timezone_set($tz);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The start date must be before the end date.
     */
    public function testTimelineExceptionWhenStartAfterEnd()
    {
        $business = new Business([new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']])]);
        $start = new \DateTime('2015-05-25 11:00');
        $end = new \DateTime('2015-05-25 10:00');

        $business->timeline($start, $end, new \DateInterval('P1D'));
    }

    public function testTimelineWithDaysInterval()
    {
        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ]);

        $start = new \DateTime('2015-05-25 11:00'); // Monday
        $end = new \DateTime('2015-06-05 13:00'); // Friday of the next week

        $dates = $business->timeline($start, $end, new \DateInterval('P1D'));

        // Monday 25, Friday 29, Monday 1, Friday 5,

        $this->assertCount(4, $dates);

        $this->assertEquals('2015-05-25 11:00', $dates[0]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-29 10:00', $dates[1]->format('Y-m-d H:i'));
        $this->assertEquals('2015-06-01 09:00', $dates[2]->format('Y-m-d H:i'));
        $this->assertEquals('2015-06-05 10:00', $dates[3]->format('Y-m-d H:i'));
    }

    public function testTimelineWithDaysIntervalAndHolidays()
    {
        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ], [new \DateTime('2015-06-01')]);

        $start = new \DateTime('2015-05-24 11:00'); // Sunday
        $end = new \DateTime('2015-06-05 13:00'); // Friday of the next week

        $dates = $business->timeline($start, $end, new \DateInterval('P1D'));

        // Monday 25, Friday 29, Monday 1, Friday 5,

        $this->assertCount(3, $dates);

        $this->assertEquals('2015-05-25 09:00', $dates[0]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-29 10:00', $dates[1]->format('Y-m-d H:i'));
        $this->assertEquals('2015-06-05 10:00', $dates[2]->format('Y-m-d H:i'));
    }

    public function testTimelineWithHoursInterval()
    {
        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ]);

        $start = new \DateTime('2015-05-25 11:30');
        $end = new \DateTime('2015-05-25 17:00');

        $dates = $business->timeline($start, $end, new \DateInterval('PT1H'));

        $this->assertEquals('2015-05-25 11:30', $dates[0]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 12:30', $dates[1]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 14:00', $dates[2]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 15:00', $dates[3]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 16:00', $dates[4]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 17:00', $dates[5]->format('Y-m-d H:i'));
    }

    public function testTimelineCustomTimezone()
    {
        $tz = date_default_timezone_get();
        date_default_timezone_set('Europe/Paris');

        $business = new Business([
            new Day(Days::MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            new Day(Days::FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
        ]);

        // Monday "2015-05-25 10:00:00" in Europe/Paris
        $start = new \DateTime('2015-05-24 22:00', new \DateTimeZone('Pacific/Tahiti'));
        $end = new \DateTime('2015-05-25 17:00');

        $dates = $business->timeline($start, $end, new \DateInterval('PT1H'));

        $this->assertEquals('2015-05-25 10:00', $dates[0]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 11:00', $dates[1]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 12:00', $dates[2]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 13:00', $dates[3]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 14:00', $dates[4]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 15:00', $dates[5]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 16:00', $dates[6]->format('Y-m-d H:i'));
        $this->assertEquals('2015-05-25 17:00', $dates[7]->format('Y-m-d H:i'));

        date_default_timezone_set($tz);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage At least one day must be added
     */
    public function testExceptionEmptyDays()
    {
        new Business([]);
    }
}
