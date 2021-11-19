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
}