<?php

declare(strict_types=1);

namespace App\CommissionApp\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\CommissionApp\Service\Operation;
use Symfony\Component\Console\Application;
use Faker\Factory;
use App\CommissionApp\Service\OperationsHistory;

class OperationTest extends TestCase
{
    /**
     * @var Operation
     */
    private $operation;

    /**
     * @var Faker
     */
    private $faker;

    public function setUp()
    {
        
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param string $expectation
     *
     * @dataProvider dataProviderForAddTesting
     */
    /* public function testAdd(string $leftOperand, string $rightOperand, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->math->add($leftOperand, $rightOperand)
        );
    }

    public function dataProviderForAddTesting(): array
    {
        return [
            'add 2 natural numbers' => ['1', '2', '3'],
            'add negative number to a positive' => ['-1', '2', '1'],
            'add natural number to a float' => ['1', '1.05123', '2.05'],
        ];
    } */

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param string $expectation
     *
     * @dataProvider dataProviderForAddTesting
     */
    public function test($array)
    {
        $operation = new Operation($array);

        $operationsHistory = new OperationsHistory();

        $this->assertEquals(0.60, $operation->calculatePrivateWithdrawCommission($operationsHistory));
    }

    public function dataProviderForAddTesting(): array
    {
        return [
            'Private withdrawal request' => [
                ['2014-12-31', '4', 'private', 'withdraw',1200.00,'EUR']
            ],
        ];
    }
}
