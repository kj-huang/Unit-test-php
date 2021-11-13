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
        if ($this->isStartDateLessThanEndDate($start, $end)) {
            return 0;
        }
        $startDate = date("Ym", strtotime($start));
        $endDate = date("Ym", strtotime($end));

        if ($startDate === $endDate) {
            foreach ($this->queries as $item) {
                if ($item["YearMonth"] === $startDate) {
                    list($dateNumber, $daysInMonth) = $this->getSameMonthPercentage($start, $end, $startDate);
                    return floor($item["Amount"] * $dateNumber / $daysInMonth);
                }
            }
        } else {
            $flag = 0;
            $budget = 0;

            $startdaysInMonth = Carbon::parse($start)->daysInMonth;
            $endDaysInMonth = Carbon::parse($end)->daysInMonth;

            $dateNumber1 = Carbon::parse($start)->day;
            $dateNumber2 = Carbon::parse($end)->day;

            $startTotal = $startdaysInMonth - $dateNumber1 + 1;
            $endTotal = $dateNumber2;

            foreach ($this->queries as $item) {
                if ($item["YearMonth"] === $startDate) {
                    $flag = 1;
                    $budget += $item["Amount"] * $startTotal / $startdaysInMonth;
                } else if (Carbon::create(substr($item["YearMonth"], 0, 4), substr($item["YearMonth"], 4, 2))->between($start, $end) && $item["YearMonth"] !== $endDate) {
                    $budget += $item["Amount"];
                }
                if ($flag === 1 && $item["YearMonth"] === $endDate) {
                    $budget += $item["Amount"] * $endTotal / $endDaysInMonth;
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
    protected function isStartDateLessThanEndDate(string $start, string $end): bool
    {
        return Carbon::parse($start)->diffInDays($end, false) < 0;
    }
}