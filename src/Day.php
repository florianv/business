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
 * Represents a business day.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Day
{
    /**
     * @var TimeInterval[]
     */
    private $openingIntervals;
    private $dayOfWeek;

    /**
     * Constructor.
     *
     * @param integer $dayOfWeek        The day of week
     * @param array   $openingIntervals The opening intervals
     */
    public function __construct($dayOfWeek, array $openingIntervals)
    {
        $this->setDayOfWeek($dayOfWeek);
        $this->setOpeningIntervals($openingIntervals);
    }

    /**
     * Gets the day of week.
     *
     * @return integer
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * Gets the closest opening time before the given time (including it).
     *
     * @param Time $time
     *
     * @return Time|null
     */
    public function getClosestOpeningTimeBefore(Time $time)
    {
        foreach ($this->openingIntervals as $openingInterval) {
            if ($openingInterval->contains($time)) {
                return $time;
            }
        }

        $closestTime = null;

        foreach (array_reverse($this->openingIntervals) as $interval) {
            $distance = $time->toInteger() - $interval->getEnd()->toInteger();

            if ($distance < 0) {
                continue;
            }

            if (null === $closestTime) {
                $closestTime = $interval->getEnd();
            }

            if ($distance < $time->toInteger() - $closestTime->toInteger()) {
                $closestTime = $interval->getEnd();
            }
        }

        return $closestTime;
    }

    /**
     * Gets the closest opening time after the given time (including it).
     *
     * @param Time $time
     *
     * @return Time|null
     */
    public function getClosestOpeningTimeAfter(Time $time)
    {
        foreach ($this->openingIntervals as $openingInterval) {
            if ($openingInterval->contains($time)) {
                return $time;
            }
        }

        $closestTime = null;

        foreach ($this->openingIntervals as $interval) {
            $distance = $interval->getStart()->toInteger() - $time->toInteger();

            if ($distance < 0) {
                continue;
            }

            if (null === $closestTime) {
                $closestTime = $interval->getStart();
            }

            if ($distance < $closestTime->toInteger() - $time->toInteger()) {
                $closestTime = $interval->getStart();
            }
        }

        return $closestTime;
    }

    /**
     * Checks if the given time is within opening hours of this day.
     *
     * @param Time $time
     *
     * @return bool
     */
    public function isTimeWithinOpeningHours(Time $time)
    {
        foreach ($this->openingIntervals as $interval) {
            if ($interval->contains($time)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the closing time of the day.
     *
     * @return Time
     */
    public function getOpeningTime()
    {
        return $this->openingIntervals[0]->getStart();
    }

    /**
     * Gets the closing time of the day.
     *
     * @return Time
     */
    public function getClosingTime()
    {
        return end($this->openingIntervals)->getEnd();
    }

    private function setDayOfWeek($dayOfWeek)
    {
        if (!in_array($dayOfWeek, Days::toArray())) {
            throw new \InvalidArgumentException(sprintf('Invalid day of week "%s".', $dayOfWeek));
        }

        $this->dayOfWeek = $dayOfWeek;
    }

    private function setOpeningIntervals(array $openingIntervals)
    {
        if (empty($openingIntervals)) {
            throw new \InvalidArgumentException('The day must have at least one opening interval.');
        }

        $this->openingIntervals = array();

        foreach ($openingIntervals as $openingInterval) {
            if (!is_array($openingInterval) || !isset($openingInterval[0]) || !isset($openingInterval[1])) {
                throw new \InvalidArgumentException(
                    'Each interval must be an array containing opening and closing times.'
                );
            }

            $this->openingIntervals[] = TimeInterval::fromString($openingInterval[0], $openingInterval[1]);
        }

        usort($this->openingIntervals, function (TimeInterval $a, TimeInterval $b) {
            return ($a->getStart() > $b->getStart()) ? 1 : -1;
        });
    }
}
