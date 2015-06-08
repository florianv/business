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
 * Represents a special day.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class SpecialDay extends AbstractDay implements \Serializable
{
    private $openingIntervalsCache = [];
    private $openingIntervalsEvaluator;

    /**
     * Constructor.
     *
     * @param integer  $dayOfWeek                 The day of week
     * @param callable $openingIntervalsEvaluator A callable to evaluate opening intervals of the day
     */
    public function __construct($dayOfWeek, callable $openingIntervalsEvaluator)
    {
        $this->setDayOfWeek($dayOfWeek);
        $this->openingIntervalsEvaluator = $openingIntervalsEvaluator;
    }

    /**
     * {@inheritdoc}
     */
    public function getClosestOpeningTimeBefore(Time $time, \DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::getClosestOpeningTimeBefore($time, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getClosestOpeningTimeAfter(Time $time, \DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::getClosestOpeningTimeAfter($time, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function isTimeWithinOpeningHours(Time $time, \DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::isTimeWithinOpeningHours($time, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getOpeningTime(\DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::getOpeningTime($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getClosingTime(\DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::getClosingTime($context);
    }

    private function evaluateOpeningIntervals(\DateTime $context)
    {
        $contextHash = $context->format(\DateTime::ISO8601);

        if (!isset($this->openingIntervalsCache[$contextHash])) {
            $intervals = call_user_func($this->openingIntervalsEvaluator, $context);

            if (!is_array($intervals)) {
                throw new \RuntimeException('The special day evaluator must return an array of opening intervals.');
            }

            $this->openingIntervalsCache[$contextHash] = $intervals;
        }

        $this->setOpeningIntervals($this->openingIntervalsCache[$contextHash]);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            $this->dayOfWeek,
            $this->openingIntervalsCache,
            $this->getSerializer()->serialize($this->openingIntervalsEvaluator)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        list($this->dayOfWeek, $this->openingIntervalsCache) = $data;
        $this->openingIntervalsEvaluator = $this->getSerializer()->unserialize($data[2]);
    }

    /**
     * @return \SuperClosure\Serializer
     */
    private function getSerializer()
    {
        if (!class_exists('\SuperClosure\Serializer')) {
            throw new \RuntimeException('You must install "jeremeamia/superclosure" in order to serialize a special day.');
        }

        return new \SuperClosure\Serializer();
    }
}
