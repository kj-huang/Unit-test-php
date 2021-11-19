<?php

namespace App;

use Carbon\Carbon;
use function floor;
use const false;

class BudgetService
{

    private $queries;
    private $budgetRepo;

    public function __construct($budgetRepo)
    {
        $this->budgetRepo = $budgetRepo;
    }

    public function execute()
    {
        $this->queries = $this->budgetRepo->getAll();
    }

    public function query(string $start, string $end)
    {
        if ($this->isInvalidRange($start, $end)) {
            return 0;
        }
        $budget = 0;

        foreach ($this->queries as $b => $budgetEntity) {
            $overlappingDays = $this->getOverlappingDays($budgetEntity, new Period($start, $end));
            $budget += floor($budgetEntity->getAmount() * $overlappingDays / $budgetEntity->currentDaysInMonth());
        }

        return $budget;
    }

    /**
     * @param string $start
     * @param string $end
     * @return bool
     */
    protected function isInvalidRange(string $start, string $end): bool
    {
        return Carbon::parse($start)->diffInDays($end, false) < 0;
    }


    /**
     * @param $budgetEntity
     * @param Period $period
     * @return int|mixed
     */
    protected function getOverlappingDays($budgetEntity, Period $period)
    {
        $current = $budgetEntity->getFormatCurrentDateTime();
        $overlappingEnd = 0;
        $overlappingStart = 0;
        if ($period->isTargetStartMonth($current)) {
            if ($period->getStartMonth() !== $period->getEndMonth()) {
                $overlappingEnd = $budgetEntity->getLastDay();
                $overlappingStart = $period->getStartDate();
            } else {
                $overlappingEnd = $budgetEntity->getLastDay();
                $overlappingStart = $budgetEntity->getLastDay() - $period->getEndDate() + 1;
            }
        } else if ($period->isInMiddleMonth($current)) {
            $overlappingStart = $budgetEntity->getFirstDay();
            $overlappingEnd = $budgetEntity->getLastDay();
        } else if ($period->isTargetEndMonth($current)) {
            $overlappingStart = $budgetEntity->getFirstDay();
            $overlappingEnd = $period->getEndDate();
        }

        return $overlappingEnd - $overlappingStart > 0 ? $overlappingEnd - $overlappingStart + 1 : 0;
    }
}

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