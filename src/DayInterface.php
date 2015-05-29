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
 * Contract for days.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface DayInterface
{
    /**
     * Gets the day of week.
     *
     * @return int
     */
    public function getDayOfWeek();

    /**
     * Gets the opening time of the day.
     *
     * @param \DateTime $context The date context
     *
     * @return Time
     */
    public function getOpeningTime(\DateTime $context);

    /**
     * Gets the closing time of the day.
     *
     * @param \DateTime $context The date context
     *
     * @return Time
     */
    public function getClosingTime(\DateTime $context);

    /**
     * Gets the closest opening time before the given time (including it).
     *
     * @param Time      $time    The time
     * @param \DateTime $context The date context
     *
     * @return Time|null
     */
    public function getClosestOpeningTimeBefore(Time $time, \DateTime $context);

    /**
     * Gets the closest opening time after the given time (including it).
     *
     * @param Time      $time    The time
     * @param \DateTime $context The date context
     *
     * @return Time|null
     */
    public function getClosestOpeningTimeAfter(Time $time, \DateTime $context);

    /**
     * Checks if the given time is within opening hours of the day.
     *
     * @param Time      $time    The time
     * @param \DateTime $context The date context
     *
     * @return bool
     */
    public function isTimeWithinOpeningHours(Time $time, \DateTime $context);
}
