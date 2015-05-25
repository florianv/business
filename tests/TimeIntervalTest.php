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

class TimeIntervalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The opening time "08:00" must be before the closing time "08:00"
     */
    public function testFromStringOpeningEqualClosing()
    {
        TimeInterval::fromString('08:00', '08:00');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The opening time "18:00" must be before the closing time "08:00"
     */
    public function testFromStringOpeningAfterClosing()
    {
        TimeInterval::fromString('18:00', '08:00');
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
}
