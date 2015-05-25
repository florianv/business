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
 * Default implementation of BusinessInterface.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Business implements BusinessInterface
{
    private $days;
    private $holidays;
    private $timezone;

    /**
     * Creates a new business.
     *
     * @param Day[]      $days
     * @param \DateTime[]        $holidays
     * @param \DateTimeZone|null $timezone
     */
    public function __construct(array $days, array $holidays = array(), \DateTimeZone $timezone = null)
    {
        $this->setDays($days);
        $this->setHolidays($holidays);
        $this->timezone = $timezone ?: new \DateTimeZone(date_default_timezone_get());
    }

    /**
     * {@inheritdoc}
     */
    public function closest(\DateTime $date, $mode = self::CLOSEST_NEXT)
    {
        $tmpDate = clone $date;
        $tmpDate->setTimezone($this->timezone);

        if (self::CLOSEST_LAST === $mode) {
            return $this->getClosestDateBefore($tmpDate);
        }

        return $this->getClosestDateAfter($tmpDate);
    }

    /**
     * {@inheritdoc}
     */
    public function within(\DateTime $date)
    {
        $tmpDate = clone $date;
        $tmpDate->setTimezone($this->timezone);

        if (!$this->isHoliday($tmpDate) && null !== $day = $this->getDay((int) $tmpDate->format('N'))) {
            return $day->isTimeWithin(Time::fromDate($tmpDate));
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function timeline(\DateTime $start, \DateTime $end, \DateInterval $interval)
    {
        if ($start >= $end) {
            throw new \LogicException('The start date must be before the end date.');
        }

        $tmpStart = clone $start;
        $tmpStart->setTimezone($this->timezone);

        $tmpEnd = clone $end;
        $tmpEnd->setTimezone($this->timezone);

        $dates = array();
        $lastDate = $tmpStart;

        while (true) {
            $date = $this->getClosestDateAfter($lastDate);

            if ($date > $tmpEnd) {
                break;
            }

            $dates[] = $date;
            $lastDate = clone $date;
            $lastDate->add($interval);
        }

        return $dates;
    }

    /**
     * Gets the closest business date before the given date.
     *
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    private function getClosestDateBefore(\DateTime $date)
    {
        $tmpDate = clone $date;
        $dayOfWeek = (int) $tmpDate->format('N');
        $time = Time::fromDate($tmpDate);

        if (!$this->isHoliday($tmpDate) && null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestOpeningTimeBefore($time)) {
                $tmpDate->setTime($closestTime->getHours(), $closestTime->getMinutes());

                return $tmpDate;
            }
        }

        $tmpDate = $this->getDateBefore($tmpDate);

        while ($this->isHoliday($tmpDate)) {
            $tmpDate = $this->getDateBefore($tmpDate);
        }

        $closestDay = $this->getClosestDayBefore((int) $tmpDate->format('N'));
        $closingTime = $closestDay->getClosingTime();
        $tmpDate->setTime($closingTime->getHours(), $closingTime->getMinutes());

        return $tmpDate;
    }

    /**
     * Gets the business date before the given date (excluding holidays).
     *
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    private function getDateBefore(\DateTime $date)
    {
        $tmpDate = clone $date;
        $tmpDate->modify('-1 day');

        $dayOfWeek = (int) $tmpDate->format('N');
        $closestDay = $this->getClosestDayBefore($dayOfWeek);

        if ($closestDay->getDayOfWeek() !== $dayOfWeek) {
            $tmpDate->modify(sprintf('last %s', Days::toString($closestDay->getDayOfWeek())));
        }

        return $tmpDate;
    }

    /**
     * Gets the closest business date after the given date.
     *
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    private function getClosestDateAfter(\DateTime $date)
    {
        $tmpDate = clone $date;
        $dayOfWeek = (int) $tmpDate->format('N');
        $time = Time::fromDate($tmpDate);

        if (!$this->isHoliday($tmpDate) && null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestOpeningTimeAfter($time)) {
                $tmpDate->setTime($closestTime->getHours(), $closestTime->getMinutes());

                return $tmpDate;
            }
        }

        $tmpDate = $this->getDateAfter($tmpDate);

        while ($this->isHoliday($tmpDate)) {
            $tmpDate = $this->getDateAfter($tmpDate);
        }

        // We set the time to the opening time of this day
        $closestDay = $this->getClosestDayBefore((int) $tmpDate->format('N'));
        $closingTime = $closestDay->getOpeningTime();
        $tmpDate->setTime($closingTime->getHours(), $closingTime->getMinutes());

        return $tmpDate;
    }

    /**
     * Gets the business date after the given date (excluding holidays).
     *
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    private function getDateAfter(\DateTime $date)
    {
        $tmpDate = clone $date;
        $tmpDate->modify('+1 day');

        $dayOfWeek = (int) $tmpDate->format('N');
        $closestDay = $this->getClosestDayAfter($dayOfWeek);

        if ($closestDay->getDayOfWeek() !== $dayOfWeek) {
            $tmpDate->modify(sprintf('next %s', Days::toString($closestDay->getDayOfWeek())));
        }

        return $tmpDate;
    }

    /**
     * Gets the closest business day before a given day number (including it).
     *
     * @param integer $dayNumber
     *
     * @return Day|null
     */
    private function getClosestDayBefore($dayNumber)
    {
        if (null !== $day = $this->getDay($dayNumber)) {
            return $day;
        }

        return $this->getDayBefore($dayNumber);
    }

    /**
     * Gets the closest business day after a given day number (including it).
     *
     * @param integer $dayNumber
     *
     * @return Day|null
     */
    private function getClosestDayAfter($dayNumber)
    {
        if (null !== $day = $this->getDay($dayNumber)) {
            return $day;
        }

        return $this->getDayAfter($dayNumber);
    }

    /**
     * Gets the business day before the day number.
     *
     * @param integer $dayNumber
     *
     * @return Day|null
     */
    private function getDayBefore($dayNumber)
    {
        $tmpDayNumber = $dayNumber;

        for ($i = 0; $i < 6; $i++) {
            $tmpDayNumber = (Days::MONDAY === $tmpDayNumber) ? Days::SUNDAY : --$tmpDayNumber;

            if (null !== $day = $this->getDay($tmpDayNumber)) {
                return $day;
            }
        }

        return $this->getDay($dayNumber);
    }

    /**
     * Gets the business day after the day number.
     *
     * @param integer $dayNumber
     *
     * @return Day|null
     */
    private function getDayAfter($dayNumber)
    {
        $tmpDayNumber = $dayNumber;

        for ($i = 0; $i < 6; $i++) {
            $tmpDayNumber = (Days::SUNDAY === $tmpDayNumber) ? Days::MONDAY : ++$tmpDayNumber;

            if (null !== $day = $this->getDay($tmpDayNumber)) {
                return $day;
            }
        }

        return $this->getDay($dayNumber);
    }

    private function getDay($dayNumber)
    {
        return isset($this->days[$dayNumber]) ? $this->days[$dayNumber] : null;
    }

    private function addDay(Day $day)
    {
        $this->days[$day->getDayOfWeek()] = $day;
    }

    private function setDays(array $days)
    {
        if (empty($days)) {
            throw new \InvalidArgumentException('At least one day must be added.');
        }

        $this->days = array();

        foreach ($days as $day) {
            $this->addDay($day);
        }
    }

    private function addHoliday(\DateTime $holiday)
    {
        $this->holidays[$holiday->format('Y-m-d')] = $holiday;
    }

    private function setHolidays(array $holidays)
    {
        $this->holidays = array();

        foreach ($holidays as $holiday) {
            $this->addHoliday($holiday);
        }
    }

    private function isHoliday(\DateTime $date)
    {
        return isset($this->holidays[$date->format('Y-m-d')]);
    }
}
