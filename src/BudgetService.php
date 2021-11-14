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

        foreach ($this->queries as $item) {
            $current = substr($item["YearMonth"], 0, 4) . "-" . substr($item["YearMonth"], 4, 2);

//            $overlappingDays = 0;

            $overlappingEnd = 0;
            $overlappingStart = 0;
            if ($this->isTargetMonth($current, Carbon::parse($start)->format("Y-m"))) {
                if (Carbon::parse($start)->month !== Carbon::parse($end)->month) {
                    $overlappingEnd = Carbon::parse($start)->endOfMonth()->day;
                    $overlappingStart = Carbon::parse($start)->day;
//                    $overlappingDays = $overlappingEnd - $overlappingStart + 1;
                } else {
                    $overlappingEnd = Carbon::parse($start)->endOfMonth()->day;
                    $overlappingStart = Carbon::parse($start)->endOfMonth()->day - Carbon::parse($end)->day + 1;
//                    $overlappingDays = $overlappingEnd - $overlappingStart + 1;
                }
            } else if ($this->isInMiddleMonth($current, $start, $end)) {
                $overlappingStart = Carbon::parse($current)->startOfMonth()->day;
                $overlappingEnd = Carbon::parse($current)->endOfMonth()->day;
//                $overlappingDays = $overlappingEnd - $overlappingStart + 1;
            } else if ($this->isTargetMonth($current, Carbon::parse($end)->format("Y-m"))) {
                $overlappingStart = Carbon::parse($end)->startOfMonth()->day;
                $overlappingEnd = Carbon::parse($end)->day;
//                $overlappingDays = $overlappingEnd - $overlappingStart + 1;
            }
            if ($overlappingEnd - $overlappingStart <= 0) {
                continue;
            }
            $overlappingDays = $overlappingEnd - $overlappingStart + 1;
            $budget += floor($item["Amount"] * $overlappingDays / Carbon::parse($current)->daysInMonth);
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
        return Carbon::parse($yearMonth)->between($start, $end) && Carbon::parse($yearMonth)->format("Y-m") !== Carbon::parse($end)->format("Y-m");;
    }
}