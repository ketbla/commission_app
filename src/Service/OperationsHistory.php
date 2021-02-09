<?php

declare(strict_types=1);

namespace App\CommissionApp\Service;

class OperationsHistory
{
    public $operations = [];

    public $users = [];

    public function __construct()
    {
    }

    public function addOperation(Operation $operation)
    {
        array_push($this->operations, $operation);
    }

    public function whereUser($filterBy)
    {
        return array_filter($this->operations, function ($operation) use ($filterBy) {
            if ($operation->user === $filterBy) {
                return true;
            }
        });
    }

    public function where(string $week, int $user)
    {
        return array_filter($this->operations, function ($operation) use ($week, $user) {
            $date = new \DateTime($operation->date);
            if ($date->format('W-o') === $week && $operation->user === $user && $operation->operation_type === 'withdraw') {
                return true;
            }
        });
    }
}
