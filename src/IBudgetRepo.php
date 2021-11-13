<?php

namespace App;

class IBudgetRepo
{
    public function getAll(){
        return [
            [
                'YearMonth' => "202111",
                'Amount' => 1000
            ],
            [
                'YearMonth' => "202112",
                'Amount' => 2000
            ],
        ];
    }
}