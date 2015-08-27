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

class HolidaysTest extends \PHPUnit_Framework_TestCase
{
    public function testIsHoliday()
    {
        $holidays = new Holidays([
            $holiday = new \DateTime('2015-05-11'),
            new DateRange(new \DateTime('2015-07-08'), new \DateTime('2015-07-21'))
        ]);

        $this->assertTrue($holidays->isHoliday($holiday));
        $this->assertTrue($holidays->isHoliday(new \DateTime('2015-07-09 10:00')));
    }

    public function testSerializeUnserialize()
    {
        $holidays = new Holidays([
            $holiday = new \DateTime('2015-05-11'),
            new DateRange(new \DateTime('2015-07-08'), new \DateTime('2015-07-21'))
        ]);

        $serialized = serialize($holidays);
        $unserialized = unserialize($serialized);

        $this->assertTrue($unserialized->isHoliday($holiday));
        $this->assertTrue($unserialized->isHoliday(new \DateTime('2015-07-09 10:00')));
    }
}
