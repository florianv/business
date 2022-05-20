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

use Business\Time;
use PHPUnit\Framework\TestCase;

class TimeTest extends TestCase
{
    public function testFromStringInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time "foo".');
        Time::fromString('foo');
    }

    public function testFromString()
    {
        $time = Time::fromString('2pm');
        $this->assertEquals(14, $time->getHours());
        $this->assertEquals(0, $time->getMinutes());
    }

    public function testFromDate()
    {
        $time = Time::fromDate(new \DateTime('2 AM'));
        $this->assertEquals(2, $time->getHours());
        $this->assertEquals(0, $time->getMinutes());
    }

    public function testToInteger()
    {
        $time = new Time('20', '00');
        $this->assertEquals(200000, $time->toInteger());

        $time = new Time('09', '30');
        $this->assertEquals(93000, $time->toInteger());
    }

    public function testIsAfterOrEqual()
    {
        $time = new Time('20', '00');
        $this->assertTrue($time->isAfterOrEqual(new Time('18', '00')));
        $this->assertFalse($time->isAfterOrEqual(new Time('22', '15')));
        $this->assertTrue($time->isAfterOrEqual(new Time('20', '00')));
    }

    public function testIsBeforeOrEqual()
    {
        $time = new Time('20', '00');
        $this->assertTrue($time->isBeforeOrEqual(new Time('22', '00')));
        $this->assertFalse($time->isBeforeOrEqual(new Time('18', '15')));
        $this->assertTrue($time->isBeforeOrEqual(new Time('20', '00')));
    }

    public function testToString()
    {
        $time = new Time('20', '30');
        $this->assertEquals('20:30:00', $time->toString());
        $time = new Time('9', '8', '7');
        $this->assertEquals('09:08:07', $time->toString());
        $time = new Time(9, 8, 7);
        $this->assertEquals('09:08:07', $time->toString());
    }

    public function testJsonSerialize()
    {
        $time = new Time('20', '30', '15');

        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/Expected/Time/testJsonSerialize.json',
            json_encode($time)
        );
    }
}
