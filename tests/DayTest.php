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

use Business\Day;
use Business\Time;
use Business\Days;

class DayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid day of week "152"
     */
    public function testExceptionInvalidDayOfWeek()
    {
        new Day(152, []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The day must have at least one opening interval
     */
    public function testExceptionEmptyOpeningInterval()
    {
        new Day(Days::MONDAY, []);
    }

    public function testGetClosestOpeningTimeBeforeInsideInterval()
    {
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeBefore(new Time('13', '00'));

        $this->assertSame(13, $closest->getHours());
        $this->assertSame(0, $closest->getMinutes());
    }

    public function testGetClosestOpeningTimeBeforeBetweenIntervals()
    {
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeBefore(new Time('14', '20'));

        $this->assertSame(14, $closest->getHours());
        $this->assertSame(0, $closest->getMinutes());
    }

    public function testGetClosestOpeningTimeBeforeOutsideIntervals()
    {
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeBefore(new Time('8', '0'));

        $this->assertNull($closest);
    }

    public function testGetClosestOpeningTimeAfterInsideInterval()
    {
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeAfter(new Time('13', '00'));

        $this->assertSame(13, $closest->getHours());
        $this->assertSame(0, $closest->getMinutes());
    }

    public function testGetClosestOpeningTimeAfterBetweenIntervals()
    {
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeAfter(new Time('14', '20'));

        $this->assertSame(14, $closest->getHours());
        $this->assertSame(30, $closest->getMinutes());
    }

    public function testGetClosestOpenTimeAfterOutsideIntervals()
    {
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeAfter(new Time('19', '00'));

        $this->assertNull($closest);
    }

    public function testGetOpeningTime()
    {
        $day = new Day(Days::MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        $this->assertEquals(9, $day->getOpeningTime()->getHours());
        $this->assertEquals(0, $day->getOpeningTime()->getMinutes());
    }

    public function testGetClosingTime()
    {
        $day = new Day(Days::MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        $this->assertEquals(18, $day->getClosingTime()->getHours());
        $this->assertEquals(30, $day->getClosingTime()->getMinutes());
    }

    public function testIsTimeWithin()
    {
        $day = new Day(Days::MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);

        $this->assertTrue($day->isTimeWithin(new Time('14', '00')));
        $this->assertTrue($day->isTimeWithin(new Time('13', '00')));
        $this->assertTrue($day->isTimeWithin(new Time('18', '30')));
        $this->assertTrue($day->isTimeWithin(new Time('15', '00')));
        $this->assertTrue($day->isTimeWithin(new Time('09', '30')));

        $this->assertFalse($day->isTimeWithin(new Time('08', '00')));
        $this->assertFalse($day->isTimeWithin(new Time('20', '00')));
    }
}
