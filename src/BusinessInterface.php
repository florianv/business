<?php

/*
 * This file is part of Business.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Business;

/**
 * Contract for the business service.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface BusinessInterface
{
    const CLOSEST_LAST = 0;
    const CLOSEST_NEXT = 1;

    /**
     * Tells if a given date is within business hours.
     *
     * @param \DateTime $date
     *
     * @return bool
     */
    public function within(\DateTime $date);

    /**
     * Returns a timeline of business dates.
     *
     * @param \DateTime     $start    The start date
     * @param \DateTime     $end      The end date
     * @param \DateInterval $interval The interval between two dates
     *
     * @return \DateTime[]
     *
     * @throws \LogicException If the start date is not earlier than end date
     */
    public function timeline(\DateTime $start, \DateTime $end, \DateInterval $interval);

    /**
     * Returns the closest business date and time from the given date.
     *
     * @param \DateTime $date The date
     * @param int       $mode The mode CLOSEST_* constant
     *
     * @return \DateTime
     *
     * The $mode works as follows:
     *
     * - CLOSEST_NEXT: Returns the closest business date after the given date (including it).
     * The time will be set to the opening time of the next interval or day.
     *
     * - CLOSEST_LAST: Returns the closest business date before the given date (including it).
     * The time will be set to the closing time of the last interval or day.
     */
    public function closest(\DateTime $date, $mode = self::CLOSEST_NEXT);

    /**
     * Returns the closest business opening hours interval endpoint for the given date.
     *
     * @param \DateTime $date The date
     * @param int       $mode The mode CLOSEST_* constant
     *
     * @return \DateTime
     *
     * The $mode works as follows:
     *
     * - CLOSEST_NEXT: Returns the closest interval date and opening time after the given date.
     * If the given date is inside the interval then the time will be set to closing time of that interval.
     *
     * - CLOSEST_LAST: Returns the closest interval date and closing time before the given date.
     * If the given date is inside the interval then the time will be set to the opening time of that interval.
     */
    public function closestIntervalEndpoint(\DateTime $date, $mode = self::CLOSEST_NEXT);
}
