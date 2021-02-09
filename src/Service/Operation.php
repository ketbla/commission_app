<?php

declare(strict_types=1);

namespace App\CommissionApp\Service;

use BenMajor\ExchangeRatesAPI\ExchangeRatesAPI;

class Operation
{
    //Weekly discount
    private $discount = 1000;

    //operation date in format Y-m-d
    public $date;

    //user's identificator, number
    public $user;

    //user's type, one of private or business
    public $user_type;

    //operation type, one of deposit or withdraw
    public $operation_type;

    //operation amount (for example 2.12 or 3)
    public $operation_ammount;

    //operation currency, one of EUR, USD, JPY
    public $operation_currency;

    public function __construct(array $inputData)
    {
        $this->date = $inputData[0];
        $this->user = (int) $inputData[1];
        $this->user_type = $inputData[2];
        $this->operation_type = $inputData[3];
        $this->operation_ammount = $inputData[4];
        $this->operation_currency = $inputData[5];
    }

    public function isDeposit()
    {
        return $this->operation_type === 'deposit' ? true : false;
    }

    public function isWithdraw()
    {
        return $this->operation_type === 'withdraw' ? true : false;
    }

    public function isBusiness()
    {
        return $this->user_type === 'business' ? true : false;
    }

    public function isPrivate()
    {
        return $this->user_type === 'private' ? true : false;
    }

    public function calculateDepositCommission()
    {
        return $this->output(($this->operation_ammount * 0.03) / 100);
    }

    public function calculateBusinessWithdrawCommission()
    {
        return $this->output(($this->operation_ammount * 0.5) / 100);
    }

    public function calculatePrivateWithdrawCommission(OperationsHistory $operationsHistory)
    {
        $taxableAmmount = $this->getTaxableAmmount($operationsHistory);

        return $this->output(($taxableAmmount * 0.3) / 100);
    }

    public function week()
    {
        //Get a week number to check if there were previous trades on the same week.
        //ALso using "o" for to see if the ISO week number (W) belongs to the previous or next year, that year is used instead.
        $date = new \DateTime($this->date);

        return $date->format('W-o');
    }

    //function to round up numbers
    public function output($value)
    {
        return number_format(round($value, 2), 2);
    }

    public function convert(string $from, string $to, float $amount)
    {
        $lookup = new ExchangeRatesAPI();

        return $lookup->setBaseCurrency($from)->convert($to, $amount);
    }

    public function getTaxableAmmount($operationsHistory)
    {
        $operationsHistory = $operationsHistory->where($this->week(), $this->user);

        if (empty($operationsHistory)) {
            $this->operation_currency !== 'EUR' ? $ammount = $this->convert($this->operation_currency, 'EUR', (float) $this->operation_ammount) : $ammount = (float) $this->operation_ammount;

            $taxable = max(0, $ammount - $this->discount);

            $this->operation_currency !== 'EUR' ? $taxable = $this->convert('EUR', $this->operation_currency, (float) $taxable) : false;

            return $taxable;
        } else {
            $totalWithdrawnAmmount = 0;

            foreach ($operationsHistory as $operation) {
                $operation->operation_currency !== 'EUR' ? $ammount = $this->convert($operation->operation_currency, 'EUR', (float) $operation->operation_ammount) : $ammount = (float) $operation->operation_ammount;

                $totalWithdrawnAmmount = $totalWithdrawnAmmount + $ammount;
            }

            $totalWithdrawnAmmount > $this->discount ? $taxable = $this->operation_ammount : false;

            if ($totalWithdrawnAmmount < $this->discount) {
                //if operation is not in EUR, convert it to EUR
                $this->operation_currency !== 'EUR' ? $ammount = $this->convert($this->operation_currency, 'EUR', (float) $this->operation_ammount) : $ammount = (float) $this->operation_ammount;

                $taxable = max(0, $totalWithdrawnAmmount + $ammount - $this->discount);

                //if original operation was not in EUR, convert taxable ammount back to orginal currency.
                $this->operation_currency !== 'EUR' ? $taxable = $this->convert('EUR', $this->operation_currency, (float) $taxable) : false;
            }

            return $taxable;
        }
    }
}
