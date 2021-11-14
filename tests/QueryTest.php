<?php

namespace Tests;

use App\Budget;
use App\BudgetService;
use App\IBudgetRepo;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    /**
     * @var BudgetService
     */
    private $budgetService;

    public function test_get_one_month_budget()
    {
        $start = "2021-11-01";
        $end = "2021-11-30";
        $this->shouldBeThatBudget($start, $end, 3000);
    }

    public function test_get_one_month_budget_with_10d()
    {
        $start = "2021-11-01";
        $end = "2021-11-10";
        $this->shouldBeThatBudget($start, $end, 1000);
    }

    public function test_get_two_month_budget()
    {
        $start = "2021-11-01";
        $end = "2021-12-31";
        $this->shouldBeThatBudget($start, $end, 6100);
    }

    public function test_get_two_month_across_different_zone()
    {
        $start = "2021-11-29";
        $end = "2021-12-03";
        $this->shouldBeThatBudget($start, $end, 500);
    }

    public function test_get_three_month_with_full_month()
    {
        $start = "2021-11-29";
        $end = "2022-01-15";
        $this->shouldBeThatBudget($start, $end, 4800);
    }

    public function test_end_date_greater_than_end_date()
    {
        $start = "2021-01-03";
        $end = "2021-01-01";
        $this->shouldBeThatBudget($start, $end, 0);
    }

    public function test_month_that_not_exist_budget()
    {
        $start = "2021-10-01";
        $end = "2021-11-05";
        $this->shouldBeThatBudget($start, $end, 500);
    }

    public function test_same_month_that_not_exist_budget()
    {
        $start = "2021-10-01";
        $end = "2021-10-31";
        $this->shouldBeThatBudget($start, $end, 0);
    }

    protected function setUp()
    {
        parent::setUp();
        $mockRepo = $this->getMockRepoData();
        $this->budgetService = new BudgetService($mockRepo);
        $this->budgetService->execute();
    }

    /**
     * @param string $start
     * @param string $end
     * @param $expected
     */
    protected function shouldBeThatBudget(string $start, string $end, $expected): void
    {
        $this->assertEquals($expected, $this->budgetService->query($start, $end));
    }

    /**
     * @return \Mockery\MockInterface
     */
    protected function getMockRepoData(): \Mockery\MockInterface
    {
        $mockRepo = \Mockery::mock(IBudgetRepo::class);
        $mockRepo->shouldReceive('getAll')->andReturn([
            "0" => new Budget("202111", 3000),
            "1" => new Budget("202112", 3100),
            "2" => new Budget("202201", 3100),
        ]);
        return $mockRepo;
    }
}
