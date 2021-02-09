<?php

declare(strict_types=1);

namespace App\CommissionApp\Commands;

use App\CommissionApp\Service\Operation;
use App\CommissionApp\Service\OperationsHistory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateCommission extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'commission:calculate';

    protected function configure()
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'Select input file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Get the filename user submitted
        $filename = $input->getArgument('filename');

        //parse the whole CSV line and each line into an object Operations::class
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $operations[] = new Operation($data);
            }
            fclose($handle);
        }

        //Load a memory class to store executed operations, this would normally reside within database or other persistent storage
        $operationsHistory = new OperationsHistory();

        //Loop through all operations and perform neccessary calculations as per the documentation
        foreach ($operations as $operation) {
            if ($operation->isDeposit()) {
                $output->writeln([$operation->calculateDepositCommission()]);
            }

            if ($operation->isWithdraw()) {
                if ($operation->isBusiness()) {
                    $output->writeln([$operation->calculateBusinessWithdrawCommission()]);
                }

                if ($operation->isPrivate()) {
                    $output->writeln([
                        $operation->calculatePrivateWithdrawCommission($operationsHistory),
                        ]);
                }
            }
            $operationsHistory->addOperation($operation);
        }

        return Command::SUCCESS;
    }
}
