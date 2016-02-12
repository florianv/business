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
final class Business implements BusinessInterface, \Serializable
{
    private $days;
    private $holidays;
    private $timezone;

    /**
     * Creates a new business.
     *
     * @param DayInterface[]            $days
     * @param Holidays|\DateTime[]|null $holidays
     * @param \DateTimeZone|null        $timezone
     */
    public function __construct(array $days, $holidays = null, \DateTimeZone $timezone = null)
    {
        if (is_array($holidays)) {
            $holidays = new Holidays($holidays);
        } elseif (is_null($holidays)) {
            $holidays = new Holidays();
        } elseif (!$holidays instanceof Holidays) {
            throw new \InvalidArgumentException('The holidays parameter must be an array of \DateTime objects, an instance of Business\Holidays or null.');
        }

        $this->setDays($days);
        $this->holidays = $holidays;
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

        if (!$this->holidays->isHoliday($tmpDate) && null !== $day = $this->getDay((int) $tmpDate->format('N'))) {
            return $day->isTimeWithinOpeningHours(Time::fromDate($tmpDate), $tmpDate);
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

        $dates = [];
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
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([$this->days, $this->holidays, $this->timezone->getName()]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        list($this->days, $this->holidays) = $data;
        $this->timezone = new \DateTimeZone($data[2]);
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

        if (!$this->holidays->isHoliday($tmpDate) && null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestOpeningTimeBefore($time, $tmpDate)) {
                $tmpDate->setTime($closestTime->getHours(), $closestTime->getMinutes(), $closestTime->getSeconds());

                return $tmpDate;
            }
        }

        $tmpDate = $this->getDateBefore($tmpDate);

        while ($this->holidays->isHoliday($tmpDate)) {
            $tmpDate = $this->getDateBefore($tmpDate);
        }

        $closestDay = $this->getClosestDayBefore((int) $tmpDate->format('N'));
        $closingTime = $closestDay->getClosingTime($tmpDate);
        $tmpDate->setTime($closingTime->getHours(), $closingTime->getMinutes(), $closingTime->getSeconds());

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

        if (!$this->holidays->isHoliday($tmpDate) && null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestOpeningTimeAfter($time, $tmpDate)) {
                $tmpDate->setTime($closestTime->getHours(), $closestTime->getMinutes(), $closestTime->getSeconds());

                return $tmpDate;
            }
        }

        $tmpDate = $this->getDateAfter($tmpDate);

        while ($this->holidays->isHoliday($tmpDate)) {
            $tmpDate = $this->getDateAfter($tmpDate);
        }

        $closestDay = $this->getClosestDayBefore((int) $tmpDate->format('N'));
        $closingTime = $closestDay->getOpeningTime($tmpDate);
        $tmpDate->setTime($closingTime->getHours(), $closingTime->getMinutes(), $closingTime->getSeconds());

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
     * @param int $dayNumber
     *
     * @return DayInterface|null
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
     * @param int $dayNumber
     *
     * @return DayInterface|null
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
     * @param int $dayNumber
     *
     * @return DayInterface|null
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
     * @param int $dayNumber
     *
     * @return DayInterface|null
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

    /**
     * Gets the day corresponding to the day number.
     *
     * @param int $dayNumber
     *
     * @return DayInterface|null
     */
    private function getDay($dayNumber)
    {
        return isset($this->days[$dayNumber]) ? $this->days[$dayNumber] : null;
    }

    /**
     * Adds a day.
     *
     * @param DayInterface $day
     */
    private function addDay(DayInterface $day)
    {
        $this->days[$day->getDayOfWeek()] = $day;
    }

    /**
     * Adds a set of days.
     *
     * @param DayInterface[] $days
     *
     * @throws \InvalidArgumentException If no days are passed
     */
    private function setDays(array $days)
    {
        if (empty($days)) {
            throw new \InvalidArgumentException('At least one day must be added.');
        }

        $this->days = [];

        foreach ($days as $day) {
            $this->addDay($day);
        }
    }
}
