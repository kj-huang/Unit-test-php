<?php

namespace App;

use Carbon\Carbon;

class Budget
{
    /**
     * @var string
     */
    private $yearMonth;
    /**
     * @var int
     */
    private $amount;

    /**
     * @param string $yearMonth
     * @param int $amount
     */
    public function __construct(string $yearMonth, int $amount)
    {
        $this->yearMonth = $yearMonth;
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    private function getYearMonth(): string
    {
        return $this->yearMonth;
    }

    /**
     * @return string
     */
    public function getFormatCurrentDateTime(): string
    {
        return substr($this->getYearMonth(), 0, 4) . "-" . substr($this->getYearMonth(), 4, 2);
    }

    /**
     * @return string
     */
    public function getFirstDay(): string
    {
        return Carbon::parse($this->getFormatCurrentDateTime())->startOfMonth()->day;
    }
}