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

use Business\Days;
use Business\SpecialDay;
use Business\Time;
use PHPUnit\Framework\TestCase;

class SpecialDayTest extends TestCase
{
    public function testExceptionInvalidDayOfWeek()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid day of week "152".');
        new SpecialDay(152, function () {});
    }

    public function testCallableNotReturningProperIntervals()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The special day evaluator must return an array of opening intervals.');
        $day = new SpecialDay(Days::MONDAY, function (\DateTime $date) {});
        $day->getClosestOpeningTimeBefore(new Time('15', '00'), new \DateTime());
    }

    public function testGetClosestOpeningTimeBefore()
    {
        $monday = new \DateTime('2015-05-25');
        $tuesday = new \DateTime('2015-05-26');

        $day = new SpecialDay(Days::MONDAY, function (\DateTime $date) {
            if ('2015-05-25' == $date->format('Y-m-d')) {
                return [['09:00', '14:00']];
            }

            return [['09:00', '18:00']];
        });

        $mondayClosest = $day->getClosestOpeningTimeBefore(new Time('15', '00'), $monday);
        $tuesdayClosest = $day->getClosestOpeningTimeBefore(new Time('18', '01'), $tuesday);

        $this->assertSame(14, $mondayClosest->getHours());
        $this->assertSame(0, $mondayClosest->getMinutes());

        $this->assertSame(18, $tuesdayClosest->getHours());
        $this->assertSame(0, $tuesdayClosest->getMinutes());
    }

    public function testGetClosestOpeningTimeAfter()
    {
        $monday = new \DateTime('2015-05-25');
        $tuesday = new \DateTime('2015-05-26');

        $day = new SpecialDay(Days::MONDAY, function (\DateTime $date) {
            if ('2015-05-25' == $date->format('Y-m-d')) {
                return [['09:00', '14:00']];
            }

            return [['12:00', '18:00']];
        });

        $mondayClosest = $day->getClosestOpeningTimeAfter(new Time('08', '59'), $monday);
        $tuesdayClosest = $day->getClosestOpeningTimeAfter(new Time('11', '55'), $tuesday);

        $this->assertSame(9, $mondayClosest->getHours());
        $this->assertSame(0, $mondayClosest->getMinutes());

        $this->assertSame(12, $tuesdayClosest->getHours());
        $this->assertSame(0, $tuesdayClosest->getMinutes());
    }

    public function testGetOpeningTime()
    {
        $monday = new \DateTime('2015-05-25');
        $tuesday = new \DateTime('2015-05-26');

        $day = new SpecialDay(Days::MONDAY, function (\DateTime $date) {
            if ('2015-05-25' == $date->format('Y-m-d')) {
                return [['09:00', '14:00'], ['06:00', '07:00']];
            }

            return [['12:00', '18:00']];
        });

        $this->assertEquals(6, $day->getOpeningTime($monday)->getHours());
        $this->assertEquals(0, $day->getOpeningTime($monday)->getMinutes());

        $this->assertEquals(12, $day->getOpeningTime($tuesday)->getHours());
        $this->assertEquals(0, $day->getOpeningTime($tuesday)->getMinutes());
    }

    public function testGetClosingTime()
    {
        $monday = new \DateTime('2015-05-25');
        $tuesday = new \DateTime('2015-05-26');

        $day = new SpecialDay(Days::MONDAY, function (\DateTime $date) {
            if ('2015-05-25' == $date->format('Y-m-d')) {
                return [['14:00', '17:00'], ['06:00', '07:00']];
            }

            return [['12:00', '18:00']];
        });

        $this->assertEquals(17, $day->getClosingTime($monday)->getHours());
        $this->assertEquals(0, $day->getClosingTime($monday)->getMinutes());

        $this->assertEquals(18, $day->getClosingTime($tuesday)->getHours());
        $this->assertEquals(0, $day->getClosingTime($tuesday)->getMinutes());
    }

    public function testIsTimeWithin()
    {
        $monday = new \DateTime('2015-05-25');

        $day = new SpecialDay(Days::MONDAY, function (\DateTime $date) {
            if ('2015-05-25' == $date->format('Y-m-d')) {
                return [['14:00', '17:00'], ['06:00', '07:00']];
            }

            return [['12:00', '18:00']];
        });

        $this->assertTrue($day->isTimeWithinOpeningHours(new Time('14', '00'), $monday));
        $this->assertTrue($day->isTimeWithinOpeningHours(new Time('16', '59'), $monday));
        $this->assertTrue($day->isTimeWithinOpeningHours(new Time('06', '59'), $monday));

        $this->assertFalse($day->isTimeWithinOpeningHours(new Time('08', '00'), $monday));
        $this->assertFalse($day->isTimeWithinOpeningHours(new Time('20', '00'), $monday));
    }

    public function testSerializeUnserialize()
    {
        $monday = new \DateTime('2015-05-25');
        $day = new SpecialDay(Days::MONDAY, function (\DateTime $date) {
            if ('2015-05-25' == $date->format('Y-m-d')) {
                return [['14:00', '17:00'], ['06:00', '07:00']];
            }

            return [['12:00', '18:00']];
        });

        $serialized = serialize($day);
        $unserialized = unserialize($serialized);

        $this->assertEquals($day->getDayOfWeek(), $unserialized->getDayOfWeek());

        $this->assertEquals(
            TestUtil::getPropertyValue($day, 'openingIntervalsCache'),
            TestUtil::getPropertyValue($unserialized, 'openingIntervalsCache')
        );

        // Instead of comparing closures we check the output is the same
        $this->assertTrue($day->isTimeWithinOpeningHours(new Time('14', '00'), $monday));
        $this->assertFalse($day->isTimeWithinOpeningHours(new Time('08', '00'), $monday));
    }

    public function testJsonSerializeWithoutOpeningIntervalsCache()
    {
        $day = new SpecialDay(
            Days::MONDAY,
            function (\DateTime $date) {
                return [['12:00', '18:00']];
            }
        );

        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/Expected/SpecialDay/testJsonSerializeWithoutOpeningIntervalsCache.json',
            json_encode($day)
        );
    }

    public function testJsonSerializeWithOpeningIntervalsCache()
    {
        $day = new SpecialDay(
            Days::MONDAY,
            function (\DateTime $date) {
                return [['12:00', '18:00']];
            }
        );

        $monday = new \DateTime('2015-05-25');
        $day->isTimeWithinOpeningHours(new Time('14', '00'), $monday);

        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/Expected/SpecialDay/testJsonSerializeWithOpeningIntervalsCache.json',
            json_encode($day)
        );
    }
}
