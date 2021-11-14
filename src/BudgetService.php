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

            $current = $budgetEntity->getFormatCurrentDateTime();
            $overlappingEnd = 0;
            $overlappingStart = 0;
            if ($this->isTargetMonth($current, Carbon::parse($start)->format("Y-m"))) {
                if (Carbon::parse($start)->month !== Carbon::parse($end)->month) {
                    $overlappingEnd = $budgetEntity->getLastDay();
                    $overlappingStart = Carbon::parse($start)->day;
                } else {
                    $overlappingEnd = $budgetEntity->getLastDay();
                    $overlappingStart = $budgetEntity->getLastDay() - Carbon::parse($end)->day + 1;
                }
            } else if ($this->isInMiddleMonth($current, $start, $end)) {
                $overlappingStart = $budgetEntity->getFirstDay();
                $overlappingEnd = $budgetEntity->getLastDay();
            } else if ($this->isTargetMonth($current, Carbon::parse($end)->format("Y-m"))) {
                $overlappingStart = $budgetEntity->getFirstDay();
                $overlappingEnd = Carbon::parse($end)->day;
            }

            //TODO code smell: but making this code can extract method later
            if ($overlappingEnd - $overlappingStart > 0) {
                $overlappingDays = $overlappingEnd - $overlappingStart + 1;
            } else {
                $overlappingDays = 0;
            }

//            $budget += floor($budgetEntity->getAmount() * $overlappingDays / Carbon::parse($current)->daysInMonth);
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
        return Carbon::parse($yearMonth)->between($start, $end) && Carbon::parse($yearMonth)->format("Y-m") !== Carbon::parse($end)->format("Y-m");;
    }
}