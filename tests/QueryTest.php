<?php

namespace Tests;

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
        $this->shouldBeThatBudget($start, $end, 1000);
    }

    public function test_get_two_month_budget()
    {
        $start = "2021-11-01";
        $end = "2021-12-31";
        $this->shouldBeThatBudget($start, $end, 3000);
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
            [
                'YearMonth' => "202111",
                'Amount' => 1000
            ],
            [
                'YearMonth' => "202112",
                'Amount' => 2000
            ]
        ]);
        return $mockRepo;
    }
}
