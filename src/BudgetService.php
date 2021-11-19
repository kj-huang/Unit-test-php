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
        $budget = 0;
        $period = new Period($start, $end);
        if ($period->isValidRange()) {
            foreach ($this->queries as $b => $budgetEntity) {
                $overlappingDays = $this->getOverlappingDays($budgetEntity, $period);
                $budget += floor($budgetEntity->getAmount() * $overlappingDays / $budgetEntity->currentDaysInMonth());
            }
        }

        return $budget;
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