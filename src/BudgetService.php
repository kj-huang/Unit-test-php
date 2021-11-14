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

            $newYearMonth = substr($item["YearMonth"], 0, 4) . "-" . substr($item["YearMonth"], 4, 2);
            $daysInMonth = Carbon::parse($newYearMonth)->daysInMonth;
            $betweenDays = 0;
            if ($this->isTargetMonth($newYearMonth, $startDate)) {
                if (Carbon::parse($start)->month !== Carbon::parse($end)->month)
                    $betweenDays = Carbon::parse($start)->endOfMonth()->day - Carbon::parse($start)->day + 1;
                else $betweenDays = Carbon::parse($start)->endOfMonth()->day - (Carbon::parse($start)->endOfMonth()->day - Carbon::parse($end)->day);
            } else if ($this->isInMiddleMonth($newYearMonth, $start, $end, $endDate)) {
                $betweenDays = Carbon::parse($newYearMonth)->endOfMonth()->day;
            } else if ($this->isTargetMonth($newYearMonth, $endDate)) {
                $betweenDays = Carbon::parse($end)->day;
            }
            $budget += floor($item["Amount"] * $betweenDays / $daysInMonth);
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
     * @param $endDate
     * @return bool
     */
    protected function isInMiddleMonth($yearMonth, string $start, string $end, $endDate): bool
    {
        return Carbon::parse($yearMonth)->between($start, $end) && Carbon::parse($yearMonth)->format("Y-m") !== $endDate;
    }
}