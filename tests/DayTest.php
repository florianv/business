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
use Business\Days;
use Business\Time;

class DayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid day of week "152".
     */
    public function testExceptionInvalidDayOfWeek()
    {
        new Day(152, []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The day must have at least one opening interval.
     */
    public function testExceptionEmptyOpeningInterval()
    {
        new Day(Days::MONDAY, []);
    }

    public function testGetClosestOpeningTimeBeforeInsideInterval()
    {
        $context = new \DateTime('2015-05-25');
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeBefore(new Time('13', '00'), $context);

        $this->assertSame(13, $closest->getHours());
        $this->assertSame(0, $closest->getMinutes());
    }

    public function testGetClosestOpeningTimeBeforeBetweenIntervals()
    {
        $context = new \DateTime('2015-05-25');
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeBefore(new Time('14', '20'), $context);

        $this->assertSame(14, $closest->getHours());
        $this->assertSame(0, $closest->getMinutes());
    }

    public function testGetClosestOpeningTimeBeforeOutsideIntervals()
    {
        $context = new \DateTime('2015-05-25');
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeBefore(new Time('8', '0'), $context);

        $this->assertNull($closest);
    }

    public function testGetClosestOpeningTimeAfterInsideInterval()
    {
        $context = new \DateTime('2015-05-25');
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeAfter(new Time('13', '00'), $context);

        $this->assertSame(13, $closest->getHours());
        $this->assertSame(0, $closest->getMinutes());
    }

    public function testGetClosestOpeningTimeAfterBetweenIntervals()
    {
        $context = new \DateTime('2015-05-25');
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeAfter(new Time('14', '20'), $context);

        $this->assertSame(14, $closest->getHours());
        $this->assertSame(30, $closest->getMinutes());
    }

    public function testGetClosestOpenTimeAfterOutsideIntervals()
    {
        $context = new \DateTime('2015-05-25');
        $day = new Day(Days::MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closest = $day->getClosestOpeningTimeAfter(new Time('19', '00'), $context);

        $this->assertNull($closest);
    }

    public function testGetOpeningTime()
    {
        $context = new \DateTime('2015-05-25');
        $day = new Day(Days::MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        $this->assertEquals(9, $day->getOpeningTime($context)->getHours());
        $this->assertEquals(0, $day->getOpeningTime($context)->getMinutes());
    }

    public function testGetClosingTime()
    {
        $context = new \DateTime('2015-05-25');
        $day = new Day(Days::MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        $this->assertEquals(18, $day->getClosingTime($context)->getHours());
        $this->assertEquals(30, $day->getClosingTime($context)->getMinutes());
    }

    public function testIsTimeWithin()
    {
        $day = new Day(Days::MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        $context = new \DateTime('2015-05-25');

        $this->assertTrue($day->isTimeWithinOpeningHours(new Time('14', '00'), $context));
        $this->assertTrue($day->isTimeWithinOpeningHours(new Time('13', '00'), $context));
        $this->assertTrue($day->isTimeWithinOpeningHours(new Time('18', '30'), $context));
        $this->assertTrue($day->isTimeWithinOpeningHours(new Time('15', '00'), $context));
        $this->assertTrue($day->isTimeWithinOpeningHours(new Time('09', '30'), $context));

        $this->assertFalse($day->isTimeWithinOpeningHours(new Time('08', '00'), $context));
        $this->assertFalse($day->isTimeWithinOpeningHours(new Time('20', '00'), $context));
    }

    public function testSerializeUnserialize()
    {
        $day = new Day(Days::MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);

        $serialized = serialize($day);
        $unserialized = unserialize($serialized);

        $this->assertEquals($day->getDayOfWeek(), $unserialized->getDayOfWeek());
        $this->assertEquals(
            TestUtil::getPropertyValue($day, 'openingIntervals'),
            TestUtil::getPropertyValue($unserialized, 'openingIntervals')
        );
    }

    public function testJsonSerialize()
    {
        $day = new Day(Days::MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);

        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/Expected/Day/testJsonSerialize.json',
            json_encode($day)
        );
    }
}
