<?php

namespace App;

use Carbon\Carbon;

class BudgetService
{

    private $queries;

    public function __construct($budgetRepo)
    {
        $this->BudgetRepo = $budgetRepo;
    }

    public function execute()
    {
        $this->queries = $this->BudgetRepo->getAll();
    }

    public function query(string $start, string $end)
    {
        if ($this->isInvalidRange($start, $end)) {
            return 0;
        }
        $startDate = date("Ym", strtotime($start));
        $endDate = date("Ym", strtotime($end));
        $budget = 0;

        if ($this->isSameMonth($startDate, $endDate)) {
            foreach ($this->queries as $item) {
                if ($this->isTargetStartMonth($item["YearMonth"], $startDate)) {
                    list($dateNumber, $daysInMonth) = $this->getSameMonthPercentage($start, $end, $startDate);
                    return $this->getMonthBudget($item["Amount"], $dateNumber, $daysInMonth);
                }
            }
        } else {
            $startedInMonth = Carbon::parse($start)->daysInMonth;
            $endDaysInMonth = Carbon::parse($end)->daysInMonth;

            $startTotal = $startedInMonth - Carbon::parse($start)->day + 1;
            $endTotal = Carbon::parse($end)->day;

            foreach ($this->queries as $item) {
                if ($this->isTargetStartMonth($item["YearMonth"], $startDate)) {
                    $budget += $this->getMonthBudget($item["Amount"], $startTotal, $startedInMonth);
                } else if ($this->isInMiddleMonth($item["YearMonth"], $start, $end, $endDate)) {
                    $budget += $item["Amount"];
                } else if ($this->isTargetEndMonth($item["YearMonth"], $endDate)) {
                    $budget += $this->getMonthBudget($item["Amount"], $endTotal, $endDaysInMonth);
                    break;
                }
            }
        }
        return floor($budget);
    }

    /**
     * @param string $start
     * @param string $end
     * @param $startDate
     * @return array
     */
    protected function getSameMonthPercentage(string $start, string $end, $startDate): array
    {
        $dateNumber = Carbon::parse($start)->diffInDays($end, false) + 1;
        $daysInMonth = Carbon::parse($startDate)->daysInMonth;
        return array($dateNumber, $daysInMonth);
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

    /**
     * @param $amount
     * @param $dateNumber
     * @param $daysInMonth
     * @return false|float
     */
    protected function getMonthBudget($amount, $dateNumber, $daysInMonth)
    {
        return floor($amount * $dateNumber / $daysInMonth);
    }
}