<?php

namespace App;

use Carbon\Carbon;

class Period
{
    /**
     * @var int
     */
    public $start;
    /**
     * @var int
     */
    public $end;

    /**
     * @param string $start
     * @param string $end
     */
    public function __construct(string $start, string $end)
    {
        $this->start = Carbon::parse($start);
        $this->end = Carbon::parse($end);

    }

    public function getStartDate()
    {
        return $this->start->day;
    }

    public function getEndDate()
    {
        return $this->end->day;
    }

    public function getStartMonth()
    {
        return $this->start->month;
    }

    public function getEndMonth()
    {
        return $this->end->month;
    }

    public function getFormatStart()
    {
        var_dump($this->start->format("Y-m"));
        return $this->start->format("Y-m");
    }

    public function getFormatEnd()
    {
        return $this->end->format("Y-m");
    }

    /**
     * @param $yearMonth
     * @param Period $period
     * @return bool
     */
    public function isInMiddleMonth($yearMonth): bool
    {
        return Carbon::parse($yearMonth)->between($this->start, $this->end) && Carbon::parse($yearMonth)->format("Y-m") !== Carbon::parse($this->end)->format("Y-m");
    }

    /**
     * @param $yearMonth
     * @return bool
     */
    public function isTargetEndMonth($yearMonth): bool
    {
        return Carbon::parse($yearMonth)->isSameMonth($this->getFormatEnd());
    }

    /**
     * @param $yearMonth
     * @return bool
     */
    public function isTargetStartMonth($yearMonth): bool
    {
        return Carbon::parse($yearMonth)->isSameMonth($this->getFormatStart());
    }

    /**
     * @return bool
     */
    public function isValidRange(): bool
    {
        return Carbon::parse($this->start)->diffInDays($this->end, false) > 0;
    }

    /**
     * @param $budgetEntity
     * @return int|mixed
     */
    public function getOverlappingDays($budgetEntity)
    {
        $current = $budgetEntity->getFormatCurrentDateTime();
        $overlappingEnd = 0;
        $overlappingStart = 0;
        if ($this->isTargetStartMonth($current)) {
            if ($this->getStartMonth() !== $this->getEndMonth()) {
                $overlappingEnd = $budgetEntity->getLastDay();
                $overlappingStart = $this->getStartDate();
            } else {
                $overlappingEnd = $budgetEntity->getLastDay();
                $overlappingStart = $budgetEntity->getLastDay() - $this->getEndDate() + 1;
            }
        } else if ($this->isInMiddleMonth($current)) {
            $overlappingStart = $budgetEntity->getFirstDay();
            $overlappingEnd = $budgetEntity->getLastDay();
        } else if ($this->isTargetEndMonth($current)) {
            $overlappingStart = $budgetEntity->getFirstDay();
            $overlappingEnd = $this->getEndDate();
        }

        return $overlappingEnd - $overlappingStart > 0 ? $overlappingEnd - $overlappingStart + 1 : 0;
    }
}