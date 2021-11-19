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
     * @param $yearMonth
     * @param $startDate
     * @return bool
     */
    protected function isTargetMonth($yearMonth, $startDate): bool
    {
        return Carbon::parse($yearMonth)->isSameMonth($startDate);
    }

    /**
     * @param $yearMonth
     * @param string $start
     * @param string $end
     * @return bool
     */
    protected function isInMiddleMonth($yearMonth, string $start, string $end): bool
    {
        return Carbon::parse($yearMonth)->between($start, $end) && Carbon::parse($yearMonth)->format("Y-m") !== Carbon::parse($end)->format("Y-m");
    }

    /**
     * @param $budgetEntity
     * @param string $start
     * @param string $end
     * @return int|mixed
     */
    protected function getOverlappingDays($budgetEntity, $period)
    {
        $current = $budgetEntity->getFormatCurrentDateTime();
        $overlappingEnd = 0;
        $overlappingStart = 0;
        if ($this->isTargetMonth($current, $period->getFormatStart())) {
            if ($period->getStartMonth() !== $period->getEndMonth()) {
                $overlappingEnd = $budgetEntity->getLastDay();
                $overlappingStart = $period->getStartDate();
            } else {
                $overlappingEnd = $budgetEntity->getLastDay();
                $overlappingStart = $budgetEntity->getLastDay() - $period->getEndDate() + 1;
            }
        } else if ($this->isInMiddleMonth($current, $period->getStart(), $period->getEnd())) {
            $overlappingStart = $budgetEntity->getFirstDay();
            $overlappingEnd = $budgetEntity->getLastDay();
        } else if ($this->isTargetMonth($current, $period->getFormatEnd())) {
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

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
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
}