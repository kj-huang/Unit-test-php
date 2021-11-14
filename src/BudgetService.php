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
        $startDate = Carbon::parse($start)->format("Y-m");
        $endDate = Carbon::parse($end)->format("Y-m");
        $budget = 0;

        foreach ($this->queries as $item) {
            $startedInMonth = Carbon::parse($start)->daysInMonth;
            $endDaysInMonth = Carbon::parse($end)->daysInMonth;

            $startTotal = $startedInMonth - Carbon::parse($start)->day + 1;
            $endTotal = Carbon::parse($end)->day;

            $newYearMonth = substr($item["YearMonth"], 0, 4) . "-" . substr($item["YearMonth"], 4, 2);

            if ($this->isTargetStartMonth($newYearMonth, $startDate)) {
                if ($this->isSameMonth($startDate, $endDate)) {
                    $betweenDays = Carbon::parse($start)->diffInDays($end, false) + 1;
                    $daysInMonth = Carbon::parse($startDate)->daysInMonth;
                    return floor($item["Amount"] * $betweenDays / $daysInMonth);
                } else {
                    $budget += floor($item["Amount"] * $startTotal / $startedInMonth);
                }
            } elseif ($this->isInMiddleMonth($newYearMonth, $start, $end, $endDate)) {
                $budget += floor($item["Amount"]);
            } else if ($this->isTargetStartMonth($newYearMonth, $endDate)) {
                $budget += floor($item["Amount"] * $endTotal / $endDaysInMonth);
            }
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
     * @param $startDate
     * @param $endDate
     * @return bool
     */
    protected function isSameMonth($startDate, $endDate): bool
    {
        return $startDate === $endDate;
    }

    /**
     * @param $yearMonth
     * @param $startDate
     * @return bool
     */
    protected function isTargetStartMonth($yearMonth, $startDate): bool
    {
        return Carbon::parse($yearMonth)->isSameMonth($startDate);
    }

    /**
     * @param $yearMonth
     * @param string $start
     * @param string $end
     * @param $endDate
     * @return bool
     */
    protected function isInMiddleMonth($yearMonth, string $start, string $end, $endDate): bool
    {
        return Carbon::parse($yearMonth)->between($start, $end) && Carbon::parse($yearMonth)->format("Y-m") !== $endDate;
    }
}