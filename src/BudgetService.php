<?php

namespace App;

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
        $startDate = date("Ym", strtotime($start));
        $endDate = date("Ym", strtotime($end));

        var_dump($startDate, $endDate);
        if ($startDate === $endDate) {
            foreach ($this->queries as $item) {
                if ($item["YearMonth"] === $startDate) {
                    return $item["Amount"];
                }
            }
        } else {
            $flag = 0;
            $budget = 0;
            foreach ($this->queries as $item) {
                if ($item["YearMonth"] === $startDate) {
                    $flag = 1;
                    $budget += $item["Amount"];
                }
                if ($flag === 1 && $item["YearMonth"] === $endDate) {
                    $budget += $item["Amount"];
                    break;
                }
            }
        }
        return $budget;
    }
}