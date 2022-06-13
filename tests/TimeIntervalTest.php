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
use Business\TimeInterval;
use PHPUnit\Framework\TestCase;

class TimeIntervalTest extends TestCase
{
    public function testConstructorOpeningEqualClosing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The opening time "08:00:00" must be before the closing time "08:00:00".');
        new TimeInterval(new Time('08', '00'), new Time('08', '00'));
    }

    public function testConstructorOpeningAfterClosing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The opening time "18:00:00" must be before the closing time "08:00:00".');
        new TimeInterval(new Time('18', '00'), new Time('08', '00'));
    }

    public function testFromString()
    {
        $interval = TimeInterval::fromString('08:00', '18:30');

        $this->assertEquals(8, $interval->getStart()->getHours());
        $this->assertEquals(0, $interval->getStart()->getMinutes());

        $this->assertEquals(18, $interval->getEnd()->getHours());
        $this->assertEquals(30, $interval->getEnd()->getMinutes());
    }

    public function testContains()
    {
        $interval = TimeInterval::fromString('08:00', '18:30');

        $this->assertTrue($interval->contains(new Time('08', '00')));
        $this->assertTrue($interval->contains(new Time('18', '30')));
        $this->assertTrue($interval->contains(new Time('09', '00')));

        $this->assertFalse($interval->contains(new Time('07', '59')));
        $this->assertFalse($interval->contains(new Time('18', '31')));
    }

    public function testJsonSerialize()
    {
        $interval = TimeInterval::fromString('08:00:01', '18:30:02');

        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/Expected/TimeInterval/testJsonSerialize.json',
            json_encode($interval)
        );
    }
}
