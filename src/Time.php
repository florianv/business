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
 * Represents a time.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Time
{
    private $hours;
    private $minutes;

    /**
     * Creates a new time.
     *
     * @param string $hours
     * @param string $minutes
     */
    public function __construct($hours, $minutes)
    {
        $this->hours = $hours;
        $this->minutes = $minutes;
    }

    /**
     * Creates a new time from a string.
     *
     * @param string $time
     *
     * @return Time
     *
     * @throws \InvalidArgumentException If the passed time is invalid
     */
    public static function fromString($time)
    {
        try {
            $date = new \DateTime($time);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Invalid time "%s".', $time));
        }

        return self::fromDate($date);
    }

    /**
     * Creates a new time from a date.
     *
     * @param \DateTime $date
     *
     * @return Time
     */
    public static function fromDate(\DateTime $date)
    {
        return new self($date->format('H'), $date->format('i'));
    }

    /**
     * Checks if this time is before or equal to an other time.
     *
     * @param Time $other
     *
     * @return bool
     */
    public function isBeforeOrEqual(Time $other)
    {
        return $this->toInteger() <= $other->toInteger();
    }

    /**
     * Checks if this time is after or equal to an other time.
     *
     * @param Time $other
     *
     * @return bool
     */
    public function isAfterOrEqual(Time $other)
    {
        return $this->toInteger() >= $other->toInteger();
    }

    /**
     * Gets the hours.
     *
     * @return int
     */
    public function getHours()
    {
        return (int) $this->hours;
    }

    /**
     * Gets the minutes.
     *
     * @return int
     */
    public function getMinutes()
    {
        return (int) $this->minutes;
    }

    /**
     * Returns an integer representation of the time.
     *
     * @return integer
     */
    public function toInteger()
    {
        return (int) $this->hours.$this->minutes;
    }

    /**
     * Returns an integer representation of the time.
     *
     * @return int
     */
    public function toString()
    {
        return sprintf('%s:%s', $this->hours, $this->minutes);
    }
}
