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
 * Base day class.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractDay implements DayInterface
{
    /**
     * @var TimeInterval[]
     */
    protected $openingIntervals;
    protected $dayOfWeek;

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
     * {@inheritdoc}
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * {@inheritdoc}
     */
    public function getClosestOpeningTimeBefore(Time $time, \DateTime $context)
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
     * {@inheritdoc}
     */
    public function getClosestOpeningTimeAfter(Time $time, \DateTime $context)
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
     * {@inheritdoc}
     */
    public function isTimeWithinOpeningHours(Time $time, \DateTime $context)
    {
        foreach ($this->openingIntervals as $interval) {
            if ($interval->contains($time)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpeningTime(\DateTime $context)
    {
        return $this->openingIntervals[0]->getStart();
    }

    /**
     * {@inheritdoc}
     */
    public function getClosingTime(\DateTime $context)
    {
        return end($this->openingIntervals)->getEnd();
    }

    protected function setDayOfWeek($dayOfWeek)
    {
        if (!in_array($dayOfWeek, Days::toArray())) {
            throw new \InvalidArgumentException(sprintf('Invalid day of week "%s".', $dayOfWeek));
        }

        $this->dayOfWeek = $dayOfWeek;
    }

    protected function setOpeningIntervals(array $openingIntervals)
    {
        if (empty($openingIntervals)) {
            throw new \InvalidArgumentException('The day must have at least one opening interval.');
        }

        $this->openingIntervals = [];

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
