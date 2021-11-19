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
                $budget += floor($budgetEntity->calculateTotals($period->getOverlappingDays($budgetEntity)));
            }
        }

        return $budget;
    }
}