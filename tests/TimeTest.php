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

class TimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid time "foo".
     */
    public function testFromStringInvalid()
    {
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
        $this->assertEquals(2000, $time->toInteger());

        $time = new Time('09', '30');
        $this->assertEquals(930, $time->toInteger());
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
        $this->assertEquals('20:30', $time->toString());
    }
}
