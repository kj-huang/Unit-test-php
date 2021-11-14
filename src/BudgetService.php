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
        $startDate = Carbon::parse($start)->format("Ym");
        $endDate = Carbon::parse($end)->format("Ym");
        $budget = 0;

        foreach ($this->queries as $item) {
            $startedInMonth = Carbon::parse($start)->daysInMonth;
            $endDaysInMonth = Carbon::parse($end)->daysInMonth;

            $startTotal = $startedInMonth - Carbon::parse($start)->day + 1;
            $endTotal = Carbon::parse($end)->day;

            if ($this->isTargetStartMonth($item["YearMonth"], $startDate)) {
                if ($this->isSameMonth($startDate, $endDate)) {
                    $dateNumber1 = Carbon::parse($start)->diffInDays($end, false) + 1;
                    $daysInMonth1 = Carbon::parse($startDate)->daysInMonth;
                    list($dateNumber, $daysInMonth) = (array($dateNumber1, $daysInMonth1));
                    return floor($item["Amount"] * $dateNumber / $daysInMonth);
                } else $budget += floor($item["Amount"] * $startTotal / $startedInMonth);
            } else {
                if ($this->isInMiddleMonth($item["YearMonth"], $start, $end, $endDate)) {
                    $budget += floor($item["Amount"]);
                } else if ($this->isTargetEndMonth($item["YearMonth"], $endDate)) {
                    $budget += floor($item["Amount"] * $endTotal / $endDaysInMonth);
                }
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
        return $yearMonth === $startDate;
    }

    /**
     * @param int $flag
     * @param $yearMonth
     * @param $endDate
     * @return bool
     */
    protected function isTargetEndMonth($yearMonth, $endDate): bool
    {
        return $yearMonth === $endDate;
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
        return Carbon::create(substr($yearMonth, 0, 4), substr($yearMonth, 4, 2))->between($start, $end) && $yearMonth !== $endDate;
    }

}